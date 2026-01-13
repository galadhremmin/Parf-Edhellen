import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type IPasskeyApi from './IPasskeyApi';
import type {
    IPasskey,
    IPasskeysResponse,
    IRegistrationChallengeResponse,
    IRegistrationVerifyRequest,
    IAuthenticationChallengeResponse,
    IAuthenticationVerifyRequest,
} from './IPasskeyApi';

export default class PasskeyApiConnector implements IPasskeyApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public getPasskeys(): Promise<IPasskeysResponse> {
        return this._api.get('passkey');
    }

    public deletePasskey(id: number, password: string): Promise<void> {
        return this._api.delete<void>(
            `passkey/${id}`,
            { password },
        );
    }

    public getRegistrationChallenge(displayName: string): Promise<IRegistrationChallengeResponse> {
        return this._api.post(
            'passkey/register/challenge',
            { display_name: displayName },
        );
    }

    public verifyRegistration(payload: IRegistrationVerifyRequest): Promise<IPasskey> {
        return this._api.post(
            'passkey/register/verify',
            payload,
        );
    }

    public getAuthenticationChallenge(email: string): Promise<IAuthenticationChallengeResponse> {
        return this._api.post(
            'passkey/login/challenge',
            { email },
        );
    }

    public verifyAuthentication(payload: IAuthenticationVerifyRequest): Promise<{ success: boolean }> {
        return this._api.post(
            'passkey/login/verify',
            payload,
        );
    }
}
