import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import { IGlossEntity } from './GlossResourceApiConnector._types';

export default class ContributionResourceApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public saveGloss(args: IGlossEntity) {
        const envelope = {
            ...args,
            morph: 'gloss',
        };

        if (!!args.id && ! isNaN(args.id) && isFinite(args.id)) {
            return this._api.value.put<IGlossEntity>(`/dashboard/contribution/${args.id}`, envelope);
        }

        delete envelope.id;
        return this._api.value.post<IGlossEntity>('/dashboard/contribution', envelope);
    }
}
