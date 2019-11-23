import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import IGlossResourceApi, { IGlossEntity } from './IGlossResourceApi';

export default class GlossResourceApiConnector implements IGlossResourceApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public delete(glossId: number, replacementId: number) {
        return this._api.delete<void>(`gloss/${glossId}`, { replacementId });
    }

    public gloss(glossId: number) {
        return this._api.get<IGlossEntity>(`gloss/${glossId}`);
    }
}
