import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import { IGlossEntity } from './GlossResourceApiConnector._types';

export default class GlossResourceApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public save(args: IGlossEntity) {
        return ! isNaN(args.id) && isFinite(args.id)
            ? this._api.value.put<IGlossEntity>('gloss', args)
            : this._api.value.post<IGlossEntity>('gloss', args);
    }
}
