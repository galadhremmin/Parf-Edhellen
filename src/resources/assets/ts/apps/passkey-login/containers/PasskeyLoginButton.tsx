import { useState, useCallback } from 'react';
import type IPasskeyApi from '@root/connectors/backend/IPasskeyApi';

interface IProps {
    passkeyApi?: IPasskeyApi;
}

const PasskeyLoginButton = (props: IProps) => {
    const { passkeyApi } = props;
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handlePasskeyLogin = useCallback(async () => {
        // Get email from the form input
        const emailInput = document.getElementById('password-login-username') as HTMLInputElement;
        if (! emailInput) {
            setError('Could not find email input field');
            return;
        }

        const email = emailInput.value.trim();
        if (! email) {
            setError('Please enter your e-mail address first');
            return;
        }

        if (! email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            setError('Please enter a valid e-mail address');
            return;
        }

        if (! passkeyApi) {
            setError('API not available');
            return;
        }

        // Check WebAuthn support
        if (! window.PublicKeyCredential) {
            setError('Your browser does not support passkeys');
            return;
        }

        try {
            setError(null);
            setLoading(true);

            // Step 1: Get authentication challenge
            let challengeData;
            try {
                challengeData = await passkeyApi.getAuthenticationChallenge(email);
            } catch (challengeErr: any) {
                // Check for throttling on challenge request
                const statusCode = challengeErr?.response?.status;
                const errorData = challengeErr?.response?.data || challengeErr?.data;
                
                if (statusCode === 429 || 
                    errorData?.exception === 'Illuminate\\Http\\Exceptions\\ThrottleRequestsException' ||
                    errorData?.message === 'Too Many Attempts.') {
                    const headers = challengeErr?.response?.headers || {};
                    const retryAfter = headers['retry-after'] || headers['Retry-After'];
                    
                    if (retryAfter) {
                        const seconds = parseInt(retryAfter, 10);
                        const minutes = Math.ceil(seconds / 60);
                        throw new Error(`Too many login attempts. Please wait ${minutes} minute${minutes !== 1 ? 's' : ''} before trying again.`);
                    } else {
                        throw new Error('Too many login attempts. Please wait a minute before trying again.');
                    }
                }
                // Re-throw if not throttling
                throw challengeErr;
            }

            // Step 2: Convert challenge data to WebAuthn format
            const base64urlToArrayBuffer = (base64url: string): Uint8Array => {
                let base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
                while (base64.length % 4) {
                    base64 += '=';
                }
                const binaryString = atob(base64);
                return Uint8Array.from(binaryString, c => c.charCodeAt(0));
            };

            const allowCredentials = challengeData.allowCredentials.map(cred => ({
                id: base64urlToArrayBuffer(cred.id) as BufferSource,
                type: 'public-key' as const,
                transports: (cred.transports || []) as AuthenticatorTransport[],
            }));

            const publicKeyCredentialRequestOptions: PublicKeyCredentialRequestOptions = {
                challenge: base64urlToArrayBuffer(challengeData.challenge) as BufferSource,
                allowCredentials: allowCredentials,
                userVerification: challengeData.userVerification as UserVerificationRequirement || 'preferred',
                timeout: challengeData.timeout || 60000,
            };

            // Step 3: Call WebAuthn API
            let credential: PublicKeyCredential;
            try {
                const response = await navigator.credentials.get({
                    publicKey: publicKeyCredentialRequestOptions,
                });

                if (! response) {
                    throw new Error('No credential returned from authenticator');
                }

                credential = response as PublicKeyCredential;
            } catch (err) {
                if (err instanceof Error) {
                    if (err.name === 'NotAllowedError' || err.name === 'AbortError') {
                        throw new Error('Passkey authentication was cancelled or timed out');
                    } else if (err.name === 'NotSupportedError') {
                        throw new Error('Your browser or device does not support passkeys');
                    } else {
                        throw new Error('Passkey authentication failed: ' + err.message);
                    }
                } else {
                    throw new Error('Passkey authentication cancelled or failed');
                }
            }

            const assertionResponse = credential.response as AuthenticatorAssertionResponse;
            if (! assertionResponse) {
                throw new Error('Invalid credential response');
            }

            // Step 4: Convert response to base64
            const clientDataJSON = btoa(
                String.fromCharCode(...new Uint8Array(assertionResponse.clientDataJSON))
            );
            const authenticatorData = btoa(
                String.fromCharCode(...new Uint8Array(assertionResponse.authenticatorData))
            );
            const signature = btoa(
                String.fromCharCode(...new Uint8Array(assertionResponse.signature))
            );
            const userHandle = assertionResponse.userHandle 
                ? btoa(String.fromCharCode(...new Uint8Array(assertionResponse.userHandle)))
                : null;

            // Step 5: Verify with backend
            await passkeyApi.verifyAuthentication({
                email: email,
                sessionId: challengeData.sessionId,
                clientDataJson: clientDataJSON,
                authenticatorAssertionObject: JSON.stringify({
                    authenticatorData: authenticatorData,
                    signature: signature,
                    userHandle: userHandle,
                }),
            });

            // Success - redirect to home or intended page
            const urlParams = new URLSearchParams(window.location.search);
            const redirectUrl = urlParams.get('redirect') || '/';
            window.location.href = redirectUrl;
        } catch (err) {
            // Error message should already be user-friendly if it's a throttling error
            // (handled in the inner try-catch above)
            setError(err instanceof Error ? err.message : 'An error occurred during passkey authentication');
            setLoading(false);
        }
    }, [passkeyApi]);

    // Don't render if WebAuthn is not supported
    if (! window.PublicKeyCredential) {
        return null;
    }

    return (
        <>
            <button
                type="button"
                className="btn btn-primary"
                onClick={handlePasskeyLogin}
                disabled={loading}
            >
                {loading ? 'Authenticating...' : 'Sign in with passkey'}
            </button>
            {error && (
                <div className="alert alert-danger mt-2" style={{ fontSize: '0.875rem' }}>
                    {error}
                </div>
            )}
        </>
    );
};

export default PasskeyLoginButton;
