import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import IGlossResourceApi, { IGlossEntity } from './IGlossResourceApi';

export default class GlossResourceApiConnector implements IGlossResourceApi {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public delete(glossId: number, replacementId: number) {
        return this._api.value.delete<void>(`gloss/${glossId}`, { replacementId });
    }

    public gloss(glossId: number) {
        return this._api.value.get<IGlossEntity>(`gloss/${glossId}`);
    }
}
