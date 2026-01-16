import { useState, useEffect } from 'react';
import StaticAlert from '@root/components/StaticAlert';
import { fireEvent } from '@root/components/Component';
import { base64urlToArrayBuffer, arrayBufferToBase64 } from '@root/utilities/func/base64';
import type { IProps } from './AddPasskeyForm._types';

const AddPasskeyForm = (props: IProps) => {
    const { account, passkeyApi, existingPasskeys = [], onSuccess, onCancel, formRef, onValidationChange } = props;

    const [displayName, setDisplayName] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [step, setStep] = useState<'name' | 'register'>('name');

    // Check if name already exists
    const isNameDuplicate = (name: string): boolean => {
        const trimmedName = name.trim().toLowerCase();
        return existingPasskeys.some(p => p.displayName.toLowerCase() === trimmedName);
    };

    // Notify parent of validation state changes
    useEffect(() => {
        const trimmedName = displayName.trim();
        const canSubmit = trimmedName.length > 0 && !isNameDuplicate(trimmedName) && step === 'name' && !loading;
        void fireEvent('AddPasskeyForm', onValidationChange, canSubmit);
    }, [displayName, step, loading, onValidationChange, existingPasskeys]);

    const handleStartRegistration = async () => {
        const trimmedName = displayName.trim();
        if (! trimmedName) {
            setError('Please enter a name for your passkey');
            return;
        }

        if (isNameDuplicate(trimmedName)) {
            setError('A passkey with this name already exists. Please choose a different name.');
            return;
        }

        try {
            setLoading(true);
            setError(null);

            if (! passkeyApi) {
                throw new Error('API not available');
            }

            // Request challenge from server
            const challengeData = await passkeyApi.getRegistrationChallenge(displayName);

            // Move to registration step
            setStep('register');

            // Convert challenge data to PublicKeyCredentialCreationOptions format
            const publicKeyCredentialCreationOptions: PublicKeyCredentialCreationOptions = {
                challenge: base64urlToArrayBuffer(challengeData.challenge) as BufferSource,
                rp: challengeData.rp,
                user: {
                    id: base64urlToArrayBuffer(challengeData.user.id) as BufferSource,
                    name: challengeData.user.name,
                    displayName: challengeData.user.displayName,
                },
                pubKeyCredParams: challengeData.pubKeyCredParams.map(param => ({
                    type: 'public-key' as const,
                    alg: param.alg,
                })),
                attestation: challengeData.attestation as AttestationConveyancePreference,
                authenticatorSelection: {
                    authenticatorAttachment: challengeData.authenticatorSelection.authenticatorAttachment as AuthenticatorAttachment,
                    residentKey: challengeData.authenticatorSelection.residentKey as ResidentKeyRequirement,
                    userVerification: challengeData.authenticatorSelection.userVerification as UserVerificationRequirement,
                },
                timeout: challengeData.timeout,
            };

            // Call WebAuthn API
            let credential: PublicKeyCredential;
            try {
                const response = await navigator.credentials.create({
                    publicKey: publicKeyCredentialCreationOptions,
                });

                if (! response) {
                    throw new Error('No credential returned from authenticator');
                }

                credential = response as PublicKeyCredential;
            } catch (err) {
                // Handle user cancellation, device errors, etc.
                if (err instanceof Error) {
                    if (err.name === 'NotAllowedError' || err.name === 'AbortError') {
                        setError('Passkey registration was cancelled or timed out');
                    } else if (err.name === 'NotSupportedError') {
                        setError('Your browser or device does not support passkeys');
                    } else {
                        setError(`Passkey registration failed: ${err.message}`);
                    }
                } else {
                    setError('Passkey registration cancelled or failed');
                }
                setStep('name');
                return;
            }

            // Extract response data
            const attestationResponse = credential.response as AuthenticatorAttestationResponse;
            if (! attestationResponse) {
                setError('Invalid credential response');
                setStep('name');
                return;
            }

            // Extract transports if available (from getTransports() method)
            // Note: Many authenticators (especially platform authenticators) don't provide transport info
            const transports: string[] = [];
            if (typeof attestationResponse.getTransports === 'function') {
                const responseTransports = attestationResponse.getTransports();
                if (Array.isArray(responseTransports)) {
                    transports.push(...responseTransports);
                }
            }

            // Convert ArrayBuffers to base64 strings
            const clientDataJSON = arrayBufferToBase64(attestationResponse.clientDataJSON);
            const attestationObject = arrayBufferToBase64(attestationResponse.attestationObject);

            // Verify with backend
            await passkeyApi.verifyRegistration({
                sessionId: challengeData.sessionId,
                clientDataJson: clientDataJSON,
                attestationObject: attestationObject,
                transports: transports.length > 0 ? transports : undefined,
            });

            // Success - callback will reload passkeys
            void fireEvent('AddPasskeyForm', onSuccess);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="AddPasskeyForm">
            {error && (
                <StaticAlert type="danger" dismissable onDismiss={() => setError(null)}>
                    {error}
                </StaticAlert>
            )}

            {step === 'name' && (
                <form ref={formRef} onSubmit={(e) => { e.preventDefault(); handleStartRegistration(); }}>
                    <div className="form-group">
                        <label htmlFor="passkey-name">Passkey Name</label>
                        <input
                            id="passkey-name"
                            type="text"
                            className="form-control"
                            placeholder="e.g., My Work YubiKey"
                            value={displayName}
                            onChange={(e) => setDisplayName(e.target.value)}
                            disabled={loading}
                        />
                        <small className="form-text text-muted">
                            Give this passkey a memorable name to help identify it later. Each passkey must have a unique name.
                        </small>
                        {displayName.trim() && isNameDuplicate(displayName.trim()) && (
                            <small className="form-text text-danger">
                                A passkey with this name already exists. Please choose a different name.
                            </small>
                        )}
                    </div>
                </form>
            )}

            {step === 'register' && (
                <div className="AddPasskeyForm__instructions">
                    {loading ? (
                        <>
                            <p>Waiting for your authenticator device...</p>
                            <p>Please follow the prompts on your device to complete the registration.</p>
                        </>
                    ) : (
                        <>
                            <p>Follow the prompts on your device to complete the registration.</p>
                            <p>If nothing happens, check that your device is ready and try again.</p>
                        </>
                    )}
                    <button
                        className="btn btn-secondary mt-3"
                        onClick={() => {
                            setStep('name');
                            setError(null);
                        }}
                        disabled={loading}
                    >
                        {loading ? 'Processing...' : 'Cancel'}
                    </button>
                </div>
            )}
        </div>
    );
};

export default AddPasskeyForm;
