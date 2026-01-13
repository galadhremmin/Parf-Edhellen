<?php

namespace App\Security;

use App\Exceptions\WebAuthnException;
use App\Helpers\StringHelper;
use App\Models\Account;
use App\Models\WebAuthnCredential;
use App\Models\WebAuthnSession;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredentialSource;
use Cose\Algorithms;

/**
 * WebAuthn/FIDO2 Authentication Service
 *
 * Handles passkey registration, authentication, and management.
 */
class WebAuthnService
{
    private ?AuthenticatorAttestationResponseValidator $_attestationValidator = null;
    private ?AuthenticatorAssertionResponseValidator $_assertionValidator = null;
    private ?object $_serializer = null;

    public function __construct()
    {
        $this->initializeValidators();
    }

    /**
     * Initialize WebAuthn validators and serializer
     */
    private function initializeValidators(): void
    {
        // Create attestation statement support manager
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

        // Create serializer
        $serializerFactory = new WebauthnSerializerFactory($attestationStatementSupportManager);
        $this->_serializer = $serializerFactory->create();

        // Create ceremony step managers
        // Allow localhost as secured RP for development (HTTP is allowed on localhost)
        $rpId = config('webauthn.rp.id');
        $securedRps = $rpId === 'localhost' ? ['localhost'] : [];
        $ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        $ceremonyStepManagerFactory->setSecuredRelyingPartyId($securedRps);
        $creationCSM = $ceremonyStepManagerFactory->creationCeremony();
        $requestCSM = $ceremonyStepManagerFactory->requestCeremony();

        // Create validators
        $this->_attestationValidator = AuthenticatorAttestationResponseValidator::create(
            $attestationStatementSupportManager,
            ceremonyStepManager: $creationCSM
        );
        $this->_assertionValidator = AuthenticatorAssertionResponseValidator::create(
            ceremonyStepManager: $requestCSM
        );
    }
    /**
     * Check if a display name already exists for the given account
     *
     * @param Account $account The account to check
     * @param string $displayName The display name to check
     * @param int|null $excludeCredentialId Optional credential ID to exclude from the check (for updates)
     * @return bool True if duplicate exists, false otherwise
     */
    private function isDisplayNameDuplicate(Account $account, string $displayName, ?int $excludeCredentialId = null): bool
    {
        $query = WebAuthnCredential::where('account_id', $account->id)
            ->where('display_name', $displayName)
            ->where('is_active', true);
        
        if ($excludeCredentialId !== null) {
            $query->where('id', '!=', $excludeCredentialId);
        }
        
        return $query->exists();
    }

    /**
     * Generate a challenge for passkey registration
     *
     * @param Account $account The account to register a passkey for
     * @param string $displayName User-friendly name for the passkey
     * @return array Challenge data for the WebAuthn API
     *
     * @throws WebAuthnException
     */
    public function generateRegistrationChallenge(Account $account, string $displayName): array
    {
        // Validate display name
        if (empty($displayName) || strlen($displayName) > 255) {
            throw new WebAuthnException('Display name must be between 1 and 255 characters');
        }

        // Check for duplicate display name
        if ($this->isDisplayNameDuplicate($account, $displayName)) {
            throw new WebAuthnException('A passkey with this name already exists. Please choose a different name.');
        }

        // Generate challenge
        $challenge = $this->generateChallenge();
        $sessionId = (string) Str::uuid();

        // Create session - use challenge as the lookup key, store sessionId in challenge_data
        $session = WebAuthnSession::create([
            'challenge' => $challenge,
            'account_id' => $account->id,
            'email' => null,
            'session_type' => 'registration',
            'challenge_data' => [
                'challenge' => $challenge,
                'session_id' => $sessionId,
                'user' => [
                    'id' => base64_encode((string) $account->id),
                    'name' => $account->email,
                    'displayName' => $account->nickname,
                ],
                'rp' => [
                    'name' => config('webauthn.rp.name'),
                    'id' => config('webauthn.rp.id'),
                ],
                'pubKeyCredParams' => [
                    ['alg' => -7, 'type' => 'public-key'], // ES256
                    ['alg' => -257, 'type' => 'public-key'], // RS256
                ],
                'attestation' => config('webauthn.attestation.conveyance'),
                'authenticatorSelection' => [
                    'authenticatorAttachment' => 'platform',
                    'residentKey' => config('webauthn.authenticator.resident_key'),
                    'userVerification' => config('webauthn.authenticator.user_verification'),
                ],
                'timeout' => config('webauthn.challenge.timeout'),
                'display_name' => $displayName,
            ],
            'expires_at' => Carbon::now()->addSeconds((int) config('webauthn.challenge.session_ttl')),
        ]);

        return [
            'challenge' => $challenge,
            'user' => [
                'id' => base64_encode((string) $account->id),
                'name' => $account->email,
                'displayName' => $account->nickname,
            ],
            'rp' => [
                'name' => config('webauthn.rp.name'),
                'id' => config('webauthn.rp.id'),
            ],
            'pubKeyCredParams' => [
                ['alg' => -7, 'type' => 'public-key'],
                ['alg' => -257, 'type' => 'public-key'],
            ],
            'attestation' => config('webauthn.attestation.conveyance'),
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'residentKey' => config('webauthn.authenticator.resident_key'),
                'userVerification' => config('webauthn.authenticator.user_verification'),
            ],
            'timeout' => config('webauthn.challenge.timeout'),
            'session_id' => $sessionId,
        ];
    }

    /**
     * Verify registration response and store credential
     *
     * @param Account $account Account to register for
     * @param string $clientDataJSON Base64-encoded client data JSON
     * @param string $attestationObject Base64-encoded attestation object
     * @param string $sessionId Session ID from challenge
     * @return WebAuthnCredential The created credential
     *
     * @throws WebAuthnException
     */
    public function verifyAndStoreCredential(
        Account $account,
        string $clientDataJSON,
        string $attestationObject,
        string $sessionId,
        ?array $transportsFromRequest = null
    ): WebAuthnCredential {
        // Find the session by session_id stored in challenge_data
        $session = WebAuthnSession::where('session_type', 'registration')
            ->where('account_id', $account->id)
            ->whereJsonContains('challenge_data->session_id', $sessionId)
            ->first();

        if (! $session) {
            throw new WebAuthnException('Invalid or expired session');
        }

        // Check if session is expired
        if (! $session->isValid()) {
            $session->delete();
            throw new WebAuthnException('Session has expired');
        }

        try {
            // Decode base64 strings
            $clientDataJSONDecoded = base64_decode($clientDataJSON, true);
            if ($clientDataJSONDecoded === false) {
                throw new WebAuthnException('Invalid clientDataJSON encoding');
            }

            $attestationObjectDecoded = base64_decode($attestationObject, true);
            if ($attestationObjectDecoded === false) {
                throw new WebAuthnException('Invalid attestationObject encoding');
            }

            // Parse clientDataJSON to extract challenge and validate
            $clientData = json_decode($clientDataJSONDecoded, true);
            if (! is_array($clientData)) {
                throw new WebAuthnException('Invalid clientDataJSON format');
            }

            // Verify challenge matches session challenge
            // Both should be base64url encoded, but normalize by removing padding for comparison
            $expectedChallenge = StringHelper::convertBase64ToBase64UrlAppropriate($session->challenge_data['challenge']);
            $receivedChallenge = StringHelper::convertBase64ToBase64UrlAppropriate($clientData['challenge'] ?? '');
            if ($receivedChallenge !== $expectedChallenge) {
                throw new WebAuthnException('Challenge mismatch');
            }

            // Verify origin matches configured origin
            $expectedOrigin = config('webauthn.rp.origin');
            $receivedOrigin = $clientData['origin'] ?? null;
            if ($receivedOrigin !== $expectedOrigin) {
                throw new WebAuthnException('Origin mismatch');
            }

            // Verify type is 'webauthn.create'
            if (($clientData['type'] ?? '') !== 'webauthn.create') {
                throw new WebAuthnException('Invalid clientData type');
            }

            // Reconstruct PublicKeyCredential - the library will extract the credential ID from attestationObject
            // We need to provide a temporary ID that will be replaced by the actual one from the attestation
            // Use base64url (no padding) as expected by the WebAuthn library
            $tempCredentialId = StringHelper::convertBase64ToBase64UrlAppropriate(base64_encode(random_bytes(32)));
            
            // Convert to base64url (no padding) as expected by the WebAuthn library
            $clientDataJSONBase64url = StringHelper::convertBase64ToBase64UrlAppropriate(base64_encode($clientDataJSONDecoded));
            $attestationObjectBase64url = StringHelper::convertBase64ToBase64UrlAppropriate(base64_encode($attestationObjectDecoded));
            
            $credentialJson = json_encode([
                'id' => $tempCredentialId,
                'type' => 'public-key',
                'rawId' => $tempCredentialId,
                'response' => [
                    'clientDataJSON' => $clientDataJSONBase64url,
                    'attestationObject' => $attestationObjectBase64url,
                ]
            ], JSON_THROW_ON_ERROR);

            // Deserialize the credential using the library's serializer
            $publicKeyCredential = $this->_serializer->deserialize(
                $credentialJson,
                PublicKeyCredential::class,
                'json'
            );

            // Verify the response is an attestation response
            if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
                throw new WebAuthnException('Invalid response type: expected attestation response');
            }

            // Extract transports from the attestation response (if available)
            $attestationResponse = $publicKeyCredential->response;
            // Prefer transports from request (browser-provided), then from response, then from verified source
            $transportsFromResponse = $transportsFromRequest ?? ($attestationResponse->transports ?? []);

            // Build the creation options for verification
            $userEntity = PublicKeyCredentialUserEntity::create(
                $account->email,
                (string) $account->id,
                $account->nickname ?? $account->email
            );

            $rpEntity = PublicKeyCredentialRpEntity::create(
                config('webauthn.rp.name'),
                config('webauthn.rp.id')
            );

            // Decode challenge from base64url to bytes
            $challengeBytes = base64_decode(StringHelper::convertBase64UrlAppropriateToStandardBase64($session->challenge_data['challenge']), true);
            if ($challengeBytes === false) {
                throw new WebAuthnException('Invalid challenge encoding in session');
            }

            $creationOptions = PublicKeyCredentialCreationOptions::create(
                $rpEntity,
                $userEntity,
                $challengeBytes,
                [
                    PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256),
                    PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS256),
                ]
            );

            // Verify the attestation response
            // Allow localhost as secured RP for development (HTTP is allowed on localhost)
            $rpId = config('webauthn.rp.id');
            $securedRps = $rpId === 'localhost' ? ['localhost'] : [];
            $publicKeyCredentialSource = $this->_attestationValidator->check(
                $publicKeyCredential->response,
                $creationOptions,
                $rpId,
                $securedRps
            );

            // Extract credential data from the verified source
            $credentialId = $publicKeyCredentialSource->publicKeyCredentialId;
            $publicKey = $publicKeyCredentialSource->credentialPublicKey;
            // Use transports from response if available, otherwise from verified source
            $transports = ! empty($transportsFromResponse) ? $transportsFromResponse : ($publicKeyCredentialSource->transports ?? []);
            $counter = $publicKeyCredentialSource->counter;

            // Check if credential ID already exists (prevent duplicate registration)
            $existingCredential = WebAuthnCredential::where('credential_id', $credentialId)->first();
            if ($existingCredential) {
                throw new WebAuthnException('This passkey is already registered');
            }

            // Get display name from session
            $displayName = $session->challenge_data['display_name'] ?? 'Passkey';

            // Double-check for duplicate display name (in case of race condition)
            if ($this->isDisplayNameDuplicate($account, $displayName)) {
                throw new WebAuthnException('A passkey with this name already exists. Please choose a different name.');
            }

            // Encode public key as base64 for storage (it's binary COSE key data)
            $publicKeyEncoded = base64_encode($publicKey);

            // Store the credential
            $credential = WebAuthnCredential::create([
                'account_id' => $account->id,
                'credential_id' => $credentialId,
                'display_name' => $displayName,
                'public_key' => $publicKeyEncoded,
                'counter' => $counter,
                'transport' => ! empty($transports) ? implode(',', $transports) : null,
                'is_active' => true,
            ]);

            // Update account has_passkeys flag
            $account->update(['has_passkeys' => true]);

            // Delete the session
            $session->delete();

            return $credential;
        } catch (WebAuthnException $e) {
            // Re-throw WebAuthn exceptions as-is, but sanitize the message
            $message = $e->getMessage();
            // Ensure message is UTF-8 safe for JSON encoding
            if (!mb_check_encoding($message, 'UTF-8')) {
                $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
                if (!mb_check_encoding($message, 'UTF-8')) {
                    $message = 'Credential verification failed';
                }
            }
            throw new WebAuthnException($message, $e->getCode(), $e);
        } catch (\Exception $e) {
            // Sanitize exception message to ensure it's UTF-8 safe for JSON encoding
            $message = $e->getMessage();
            // Remove any non-UTF-8 characters
            if (!mb_check_encoding($message, 'UTF-8')) {
                $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
                if (!mb_check_encoding($message, 'UTF-8')) {
                    $message = 'Credential verification failed';
                } else {
                    $message = 'Credential verification failed: ' . $message;
                }
            } else {
                $message = 'Credential verification failed: ' . $message;
            }
            throw new WebAuthnException($message);
        }
    }

    /**
     * Generate authentication challenge for passkey login
     *
     * @param string $email Email address to authenticate
     * @return array Challenge data for the WebAuthn API
     */
    public function generateAuthenticationChallenge(string $email): array
    {
        $challenge = $this->generateChallenge();
        $sessionId = (string) Str::uuid();

        // Get account if exists (for privacy, we don't throw an error if it doesn't)
        $account = Account::where('email', $email)
            ->where('is_master_account', true)
            ->first();

        // Get credentials for this account (even if account doesn't exist, we return valid response)
        $allowCredentials = [];
        if ($account !== null) {
            // Query credentials directly to ensure we get fresh data
            $credentials = WebAuthnCredential::where('account_id', $account->id)
                ->where('is_active', true)
                ->get();
            
            $allowCredentials = $credentials->map(function (WebAuthnCredential $cred) {
                return [
                    'type' => 'public-key',
                    'id' => base64_encode($cred->credential_id),
                    'transports' => $cred->transport ? explode(',', $cred->transport) : [],
                ];
            })->all();
        }

        // Create session - store sessionId in challenge_data for lookup
        WebAuthnSession::create([
            'challenge' => $challenge,
            'account_id' => null,
            'email' => $email,
            'session_type' => 'authentication',
            'challenge_data' => [
                'challenge' => $challenge,
                'session_id' => $sessionId,
                'timeout' => config('webauthn.challenge.timeout'),
                'rpId' => config('webauthn.rp.id'),
                'userVerification' => config('webauthn.authenticator.user_verification'),
                'allowCredentials' => $allowCredentials,
            ],
            'expires_at' => Carbon::now()->addSeconds((int) config('webauthn.challenge.session_ttl')),
        ]);

        return [
            'challenge' => $challenge,
            'allowCredentials' => $allowCredentials,
            'userVerification' => config('webauthn.authenticator.user_verification'),
            'timeout' => config('webauthn.challenge.timeout'),
            'session_id' => $sessionId,
        ];
    }

    /**
     * Verify authentication response and return authenticated account
     *
     * @param string $email Email attempting to authenticate
     * @param string $clientDataJSON Base64-encoded client data JSON
     * @param string $authenticatorAssertionObject Base64-encoded authenticator data (full response)
     * @param string $sessionId Session ID from challenge
     * @return Account The authenticated account (master account if on linked account)
     *
     * @throws WebAuthnException
     */
    public function verifyAuthenticationResponse(
        string $email,
        string $clientDataJSON,
        string $authenticatorAssertionObject,
        string $sessionId
    ): Account {
        // Find the session by session_id stored in challenge_data
        $session = WebAuthnSession::where('session_type', 'authentication')
            ->where('email', $email)
            ->whereJsonContains('challenge_data->session_id', $sessionId)
            ->first();

        if (! $session) {
            throw new WebAuthnException('Invalid or expired session');
        }

        // Check if session is expired
        if (! $session->isValid()) {
            $session->delete();
            throw new WebAuthnException('Session has expired');
        }

        try {
            // Find master account
            $masterAccount = Account::where('email', $email)
                ->where('is_master_account', true)
                ->firstOrFail();

            // Decode base64 strings
            $clientDataJSONDecoded = base64_decode($clientDataJSON, true);
            if ($clientDataJSONDecoded === false) {
                throw new WebAuthnException('Invalid clientDataJSON encoding');
            }

            // Parse clientDataJSON to validate
            $clientData = json_decode($clientDataJSONDecoded, true);
            if (! is_array($clientData)) {
                throw new WebAuthnException('Invalid clientDataJSON format');
            }

            // Verify challenge matches session challenge
            // Both should be base64url encoded, but normalize by removing padding for comparison
            $expectedChallenge = StringHelper::convertBase64ToBase64UrlAppropriate($session->challenge_data['challenge']);
            $receivedChallenge = StringHelper::convertBase64ToBase64UrlAppropriate($clientData['challenge'] ?? '');
            if ($receivedChallenge !== $expectedChallenge) {
                throw new WebAuthnException('Challenge mismatch');
            }

            // Verify origin matches configured origin
            $expectedOrigin = config('webauthn.rp.origin');
            $receivedOrigin = $clientData['origin'] ?? null;
            if ($receivedOrigin !== $expectedOrigin) {
                throw new WebAuthnException('Origin mismatch');
            }

            // Verify type is 'webauthn.get'
            if (($clientData['type'] ?? '') !== 'webauthn.get') {
                throw new WebAuthnException('Invalid clientData type');
            }

            // The authenticatorAssertionObject should contain authenticatorData and signature
            // The WebAuthn API returns these as separate ArrayBuffers that the frontend should extract
            // and send as base64 strings in a JSON object: { authenticatorData: "...", signature: "..." }
            // TODO: Update frontend interface to send authenticatorData and signature separately
            // For now, try to parse as JSON first
            $assertionData = json_decode(base64_decode($authenticatorAssertionObject, true) ?: $authenticatorAssertionObject, true);
            
            // If it's not JSON or doesn't have the expected structure, throw an error
            if (! is_array($assertionData) || ! isset($assertionData['authenticatorData']) || ! isset($assertionData['signature'])) {
                throw new WebAuthnException(
                    'Invalid authenticatorAssertionObject format. ' .
                    'Expected JSON object with authenticatorData and signature fields as base64 strings.'
                );
            }

            // Extract credential ID from the response - it should be in the PublicKeyCredential.id field
            // But we don't have it yet, so we'll need to try all credentials
            // The credential ID is also in the authenticatorData, but we'll let the library extract it
            
            // Reconstruct PublicKeyCredential
            // We need the credential ID, but we don't have it yet - we'll try all credentials
            // For now, use a placeholder that will be matched during verification
            // Use base64url (no padding) as expected by the WebAuthn library
            $tempCredentialId = StringHelper::convertBase64ToBase64UrlAppropriate(base64_encode(random_bytes(32)));
            
            // Convert to base64url (no padding) as expected by the WebAuthn library
            $clientDataJSONBase64url = StringHelper::convertBase64ToBase64UrlAppropriate(base64_encode($clientDataJSONDecoded));
            $authenticatorDataBase64url = StringHelper::convertBase64ToBase64UrlAppropriate($assertionData['authenticatorData']);
            $signatureBase64url = StringHelper::convertBase64ToBase64UrlAppropriate($assertionData['signature']);
            
            $credentialJson = json_encode([
                'id' => $tempCredentialId,
                'type' => 'public-key',
                'rawId' => $tempCredentialId,
                'response' => [
                    'clientDataJSON' => $clientDataJSONBase64url,
                    'authenticatorData' => $authenticatorDataBase64url,
                    'signature' => $signatureBase64url,
                    'userHandle' => $assertionData['userHandle'] ?? null,
                ]
            ], JSON_THROW_ON_ERROR);

            // Deserialize the credential
            $publicKeyCredential = $this->_serializer->deserialize(
                $credentialJson,
                PublicKeyCredential::class,
                'json'
            );

            // Verify the response is an assertion response
            if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
                throw new WebAuthnException('Invalid response type: expected assertion response');
            }

            // Get all active credentials for this account
            $credentials = $masterAccount->activeWebauthnCredentials()->get();

            if ($credentials->isEmpty()) {
                throw new WebAuthnException('No valid credentials found for this account');
            }

            // Build the request options for verification
            $userEntity = PublicKeyCredentialUserEntity::create(
                $masterAccount->email,
                (string) $masterAccount->id,
                $masterAccount->nickname ?? $masterAccount->email
            );

            // Decode challenge from base64url to bytes
            $challengeBytes = base64_decode(StringHelper::convertBase64UrlAppropriateToStandardBase64($session->challenge_data['challenge']), true);
            if ($challengeBytes === false) {
                throw new WebAuthnException('Invalid challenge encoding in session');
            }

            $requestOptions = PublicKeyCredentialRequestOptions::create(
                $challengeBytes,
                userVerification: config('webauthn.authenticator.user_verification')
            );

            // Try to verify with each credential until one succeeds
            $verifiedCredential = null;
            $lastException = null;

            foreach ($credentials as $dbCredential) {
                try {
                    // Create a PublicKeyCredentialSource from the stored credential data
                    $credentialSource = $this->reconstructPublicKeyCredentialSource($dbCredential);

                    // Verify the assertion response
                    // The check method expects: credentialId/source, response, options, host/rpId, userHandle (string|null), securedRps
                    // Allow localhost as secured RP for development (HTTP is allowed on localhost)
                    $rpId = config('webauthn.rp.id');
                    $securedRps = $rpId === 'localhost' ? ['localhost'] : [];
                    $verifiedSource = $this->_assertionValidator->check(
                        $credentialSource,
                        $publicKeyCredential->response,
                        $requestOptions,
                        $rpId,
                        (string) $masterAccount->id, // userHandle as string
                        $securedRps
                    );

                    // Update the counter (RFC 8926: signature counter should be updated)
                    // Note: Some authenticators (especially platform authenticators) may not increment
                    // the counter on every use, so we allow equal or greater values
                    $newCounter = $verifiedSource->counter;
                    if ($newCounter < $dbCredential->counter) {
                        throw new WebAuthnException(
                            'Signature counter decreased. Possible cloned authenticator.'
                        );
                    }

                    // All checks passed
                    $verifiedCredential = $dbCredential;
                    $verifiedCredential->recordUsage($newCounter);
                    break;
                } catch (\Exception $e) {
                    // This credential didn't match, try the next one
                    $lastException = $e;
                    continue;
                }
            }

            if (! $verifiedCredential) {
                throw $lastException ?? new WebAuthnException('Authentication verification failed');
            }

            // Update account's last_passkey_auth_at
            $masterAccount->update(['last_passkey_auth_at' => Carbon::now()]);

            // Delete the session after successful authentication
            $session->delete();

            return $masterAccount;
        } catch (WebAuthnException $e) {
            // Delete session on failure to prevent accumulation
            if (isset($session)) {
                $session->delete();
            }
            // Re-throw WebAuthn exceptions as-is
            throw $e;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            // Delete session on failure
            if (isset($session)) {
                $session->delete();
            }
            throw new WebAuthnException('Account not found');
        } catch (\Exception $e) {
            // Delete session on failure
            if (isset($session)) {
                $session->delete();
            }
            throw new WebAuthnException('Authentication verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Reconstruct a PublicKeyCredentialSource from stored database credential
     *
     * @param WebAuthnCredential $dbCredential The stored credential from database
     * @return PublicKeyCredentialSource The reconstructed source for verification
     *
     * @throws WebAuthnException
     */
    private function reconstructPublicKeyCredentialSource(WebAuthnCredential $dbCredential): PublicKeyCredentialSource
    {
        try {
            // The public key is stored as a serialized COSE key
            $transports = $dbCredential->transport ? explode(',', $dbCredential->transport) : [];

            // Convert credential_id to string (it's stored as binary)
            $credentialIdString = is_string($dbCredential->credential_id) 
                ? $dbCredential->credential_id 
                : base64_encode($dbCredential->credential_id);

            // Decode public key from base64 (it's stored as base64-encoded binary COSE key)
            $publicKeyDecoded = base64_decode($dbCredential->public_key, true);
            if ($publicKeyDecoded === false) {
                throw new WebAuthnException('Invalid public key encoding in database');
            }

            // Create a PublicKeyCredentialSource from the database data
            // Note: We need to provide a Uuid for aaguid - use a default/null UUID
            $aaguid = \Symfony\Component\Uid\Uuid::fromString('00000000-0000-0000-0000-000000000000');

            return PublicKeyCredentialSource::create(
                $credentialIdString,
                'public-key',
                $transports,
                'none', // Our system accepts direct attestation only
                new \Webauthn\TrustPath\EmptyTrustPath(),
                $aaguid,
                $publicKeyDecoded,
                (string) $dbCredential->account_id,
                $dbCredential->counter
            );
        } catch (\Exception $e) {
            throw new WebAuthnException('Failed to reconstruct credential: ' . $e->getMessage());
        }
    }

    /**
     * Delete a passkey credential
     *
     * @param WebAuthnCredential $credential The credential to delete
     * @param Account $requestingAccount The account requesting deletion
     * @return void
     *
     * @throws WebAuthnException
     */
    public function deleteCredential(WebAuthnCredential $credential, Account $requestingAccount): void
    {
        // Authorization: User can only delete their own credentials
        if ($credential->account_id !== $requestingAccount->id) {
            throw new WebAuthnException('Unauthorized to delete this credential');
        }

        // Check if this is the last passkey and there's no password fallback
        $remainingCredentials = WebAuthnCredential::where('account_id', $credential->account_id)
            ->where('is_active', true)
            ->where('id', '!=', $credential->id)
            ->count();

        $account = $credential->account;
        if ($remainingCredentials === 0 && !$account->is_passworded) {
            throw new WebAuthnException(
                'Cannot delete the only passkey without a password. Add a password first or contact support.'
            );
        }

        // Delete the credential
        $credential->delete();

        // Update has_passkeys flag if no more active credentials
        $hasActiveCredentials = $account->activeWebauthnCredentials()->count() > 0;
        if (! $hasActiveCredentials) {
            $account->update(['has_passkeys' => false]);
        }
    }

    /**
     * Get all active credentials for an account
     *
     * @param Account $account The account to get credentials for
     * @return \Illuminate\Database\Eloquent\Collection<WebAuthnCredential>
     */
    public function getCredentialsForAccount(Account $account)
    {
        return $account->activeWebauthnCredentials()->get();
    }

    /**
     * Get credentials for email (for login flow)
     *
     * @param string $email The email address
     * @return \Illuminate\Database\Eloquent\Collection<WebAuthnCredential>
     */
    public function getCredentialsForEmail(string $email)
    {
        return WebAuthnCredential::whereHas('account', function ($query) use ($email) {
            $query->where('email', $email)
                ->where('is_master_account', true);
        })
        ->where('is_active', true)
        ->get();
    }

    /**
     * Clean up expired sessions
     *
     * @return int Number of deleted sessions
     */
    public function cleanupExpiredSessions(): int
    {
        return WebAuthnSession::where('expires_at', '<', Carbon::now())->delete();
    }

    /**
     * Generate a random challenge
     *
     * @return string Base64url-encoded challenge (URL-safe base64, no padding)
     */
    private function generateChallenge(): string
    {
        $randomBytes = random_bytes((int) config('webauthn.challenge.length', 32));
        // Convert base64 to base64url (URL-safe, no padding)
        $base64 = base64_encode($randomBytes);
        return StringHelper::convertBase64ToBase64UrlAppropriate($base64);
    }
}
