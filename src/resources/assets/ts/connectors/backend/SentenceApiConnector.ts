import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { ISentenceResourceApi, ISuggestGlossesForFragmentsRequest, ISuggestGlossesForFragmentsResponse } from './ISentenceResourceApi';

export default class SentenceResourceApiConnector implements ISentenceResourceApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public async suggestGlossesForFragment(args: ISuggestGlossesForFragmentsRequest): Promise<ISuggestGlossesForFragmentsResponse> {
        return this._api.post('sentence/suggest-glosses', args);
    }
}
