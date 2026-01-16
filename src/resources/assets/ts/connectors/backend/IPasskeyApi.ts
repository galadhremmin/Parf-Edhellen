export interface IPasskey {
    id: number;
    displayName: string;
    createdAt: string;
    lastUsedAt: string | null;
    transport: string;
    counter: number;
    aaguid: string | null;
}

export interface IPasskeysResponse {
    passkeys: IPasskey[];
}

export interface IDeletePasskeyRequest {
    id: number;
    password: string;
}

export interface IRegistrationChallengeRequest {
    displayName: string;
}

export interface IRegistrationChallengeResponse {
    challenge: string;
    user: {
        id: string;
        name: string;
        displayName: string;
    };
    rp: {
        name: string;
        id: string;
    };
    pubKeyCredParams: Array<{
        alg: number;
        type: string;
    }>;
    attestation: string;
    authenticatorSelection: {
        authenticatorAttachment: string;
        residentKey: string;
        userVerification: string;
    };
    timeout: number;
    sessionId: string;
}

export interface IRegistrationVerifyRequest {
    sessionId: string;
    clientDataJson: string;
    attestationObject: string;
    transports?: string[];
}

export interface IAuthenticationChallengeRequest {
    email: string;
}

export interface IAuthenticationChallengeResponse {
    challenge: string;
    allowCredentials: Array<{
        type: string;
        id: string;
        transports: string[];
    }>;
    userVerification: string;
    timeout: number;
    sessionId: string;
}

export interface IAuthenticationVerifyRequest {
    email: string;
    sessionId: string;
    clientDataJson: string;
    authenticatorAssertionObject: string;
}

export default interface IPasskeyApi {
    getPasskeys(): Promise<IPasskeysResponse>;
    deletePasskey(id: number, password: string): Promise<void>;
    getRegistrationChallenge(displayName: string): Promise<IRegistrationChallengeResponse>;
    verifyRegistration(payload: IRegistrationVerifyRequest): Promise<IPasskey>;
    getAuthenticationChallenge(email: string): Promise<IAuthenticationChallengeResponse>;
    verifyAuthentication(payload: IAuthenticationVerifyRequest): Promise<{ success: boolean }>;
}
