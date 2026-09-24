import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type ISenseApi from './ISenseApi';
import type { ISenseSuggestionsResponse } from './ISenseApi';

export default class SenseApiConnector implements ISenseApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public find(query: string, conceptId?: number): Promise<ISenseSuggestionsResponse> {
        const meaning = conceptId ? `&concept_id=${conceptId}` : '';

        return this._api.get<ISenseSuggestionsResponse>(`sense/find?q=${encodeURIComponent(query)}${meaning}`);
    }
}
