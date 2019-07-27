import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import { IGlossEntity } from './GlossResourceApiConnector._types';

export default class GlossResourceApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public gloss(glossId: number) {
        return this._api.value.get<IGlossEntity>(`gloss/${glossId}`);
    }
}
