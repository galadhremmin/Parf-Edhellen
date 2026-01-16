<?php

namespace App\Http\Controllers\Api\v3;

use App\Events\AccountSecurityActivity;
use App\Events\AccountSecurityActivityResultEnum;
use App\Exceptions\WebAuthnException;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;
use App\Models\WebAuthnCredential;
use App\Security\WebAuthnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasskeyApiController extends Controller
{
    private WebAuthnService $_webAuthnService;

    public function __construct(WebAuthnService $webAuthnService)
    {
        $this->_webAuthnService = $webAuthnService;
    }

    /**
     * Generate challenge for passkey registration
     *
     * POST /api/v3/passkey/register/challenge
     */
    public function generateRegistrationChallenge(Request $request): JsonResponse
    {
        // Must be authenticated
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $challenge = $this->_webAuthnService->generateRegistrationChallenge(
                $user,
                $validated['display_name']
            );

            return response()->json($challenge, 200);
        } catch (WebAuthnException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify registration response and store credential
     *
     * POST /api/v3/passkey/register/verify
     */
    public function verifyRegistrationResponse(Request $request): JsonResponse
    {
        // Must be authenticated
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'session_id' => ['required', 'string'],
            'client_data_json' => ['required', 'string'],
            'attestation_object' => ['required', 'string'],
            'transports' => ['nullable', 'array'],
            'transports.*' => ['string', 'in:usb,nfc,ble,internal,hybrid'],
        ]);

        try {
            $credential = $this->_webAuthnService->verifyAndStoreCredential(
                $user,
                $validated['client_data_json'],
                $validated['attestation_object'],
                $validated['session_id'],
                $validated['transports'] ?? null
            );

            // Log security event
            event(new AccountSecurityActivity(
                $user,
                'passkey_register',
                AccountSecurityActivityResultEnum::SUCCESS,
                $request->ip(),
                $request->userAgent()
            ));

            return response()->json([
                'success' => true,
                'credential' => [
                    'id' => $credential->id,
                    'display_name' => $credential->display_name,
                    'created_at' => $credential->created_at->toIso8601String(),
                    'last_used_at' => $credential->last_used_at?->toIso8601String(),
                    'transport' => $credential->transport,
                ],
            ], 200);
        } catch (WebAuthnException $e) {
            // Log failure
            event(new AccountSecurityActivity(
                $user,
                'passkey_register',
                AccountSecurityActivityResultEnum::FAILURE,
                $request->ip(),
                $request->userAgent()
            ));

            // Sanitize error message to ensure it's UTF-8 safe for JSON encoding
            $errorMessage = $e->getMessage();
            if (! mb_check_encoding($errorMessage, 'UTF-8')) {
                $errorMessage = mb_convert_encoding($errorMessage, 'UTF-8', 'UTF-8');
                if (! mb_check_encoding($errorMessage, 'UTF-8')) {
                    $errorMessage = 'Credential verification failed';
                }
            }

            return response()->json(['error' => $errorMessage], 400);
        }
    }

    /**
     * Generate challenge for passkey authentication
     *
     * POST /api/v3/passkey/login/challenge
     */
    public function generateAuthenticationChallenge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $challenge = $this->_webAuthnService->generateAuthenticationChallenge($validated['email']);

            return response()->json($challenge, 200);
        } catch (WebAuthnException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify authentication response and log user in
     *
     * POST /api/v3/passkey/login/verify
     */
    public function verifyAuthenticationResponse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'session_id' => ['required', 'string'],
            'client_data_json' => ['required', 'string'],
            'authenticator_assertion_object' => ['required', 'string'],
        ]);

        try {
            $account = $this->_webAuthnService->verifyAuthenticationResponse(
                $validated['email'],
                $validated['client_data_json'],
                $validated['authenticator_assertion_object'],
                $validated['session_id']
            );

            // Log security event
            event(new AccountSecurityActivity(
                $account,
                'passkey_auth',
                AccountSecurityActivityResultEnum::SUCCESS,
                $request->ip(),
                $request->userAgent()
            ));

            // Log the user in (using master account if credential was on linked account)
            Auth::login($account, false);

            return response()->json([
                'success' => true,
                'account' => [
                    'id' => $account->id,
                    'email' => $account->email,
                    'nickname' => $account->nickname,
                ],
            ], 200);
        } catch (WebAuthnException $e) {
            // Log failure - find account for event logging
            $account = Account::where('email', $validated['email'])
                ->where('is_master_account', true)
                ->first();

            if ($account) {
                event(new AccountSecurityActivity(
                    $account,
                    'passkey_auth',
                    AccountSecurityActivityResultEnum::FAILURE,
                    $request->ip(),
                    $request->userAgent()
                ));
            }

            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Get all passkeys for the authenticated user
     *
     * GET /api/v3/passkey
     */
    public function getPasskeys(Request $request): JsonResponse
    {
        $user = $request->user();

        $passkeys = $this->_webAuthnService->getCredentialsForAccount($user);

        return response()->json([
            'passkeys' => $passkeys->map(function (WebAuthnCredential $credential) {
                return [
                    'id' => $credential->id,
                    'display_name' => $credential->display_name,
                    'created_at' => $credential->created_at->toIso8601String(),
                    'last_used_at' => $credential->last_used_at?->toIso8601String(),
                    'transport' => $credential->transport,
                    'counter' => $credential->counter,
                    'aaguid' => $credential->aaguid,
                ];
            }),
        ], 200);
    }

    /**
     * Delete a passkey
     *
     * DELETE /api/v3/passkey/{id}
     */
    public function deletePasskey(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'password' => ['nullable', 'string'],
        ]);

        try {
            $credential = WebAuthnCredential::findOrFail($id);

            // Verify the credential belongs to the user or a linked account
            $account = $credential->account;
            if ($account->id !== $user->id && $account->master_account_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if this is the last passkey
            $remainingCredentials = WebAuthnCredential::where('account_id', $account->id)
                ->where('is_active', true)
                ->where('id', '<>', $credential->id)
                ->count();

            // If this is the last passkey and account has no password, require password confirmation
            // Use $account->is_passworded since the credential belongs to $account, not necessarily $user
            $passwordProvided = isset($validated['password']) && ! empty($validated['password']);
            if ($remainingCredentials === 0 && ! $account->is_passworded && ! $passwordProvided) {
                return response()->json(
                    ['error' => 'Cannot delete all passkeys without a password'],
                    422
                );
            }

            // Verify password if provided
            if (isset($validated['password']) && $validated['password'] && ! Hash::check($validated['password'], $user->password)) {
                return response()->json(['error' => 'Invalid password'], 401);
            }

            $this->_webAuthnService->deleteCredential($credential, $user);

            // Log security event
            event(new AccountSecurityActivity(
                $user,
                'passkey_delete',
                AccountSecurityActivityResultEnum::SUCCESS,
                $request->ip(),
                $request->userAgent()
            ));

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            // Log failure
            event(new AccountSecurityActivity(
                $user,
                'passkey_delete',
                AccountSecurityActivityResultEnum::FAILURE,
                $request->ip(),
                $request->userAgent()
            ));

            if ($e instanceof WebAuthnException) {
                return response()->json(['error' => $e->getMessage()], 400);
            }

            return response()->json(['error' => 'Failed to delete passkey'], 500);
        }
    }

}
