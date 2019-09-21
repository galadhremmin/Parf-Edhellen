import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    IContribution,
    IContributionSaveResponse,
} from './ContributionResourceApiConnector._types';
import { IGlossEntity } from './GlossResourceApiConnector._types';

export default class ContributionResourceApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public saveGloss(args: IContribution<IGlossEntity>) {
        const envelope = {
            ...args,
            morph: 'gloss',
        };
        delete envelope.id;

        if (!!args.contributionId && ! isNaN(args.contributionId) && isFinite(args.contributionId)) {
            return this._api.value.put<IContributionSaveResponse>(`/dashboard/contribution/${args.contributionId}`,
                envelope);
        }

        delete envelope.contributionId;
        return this._api.value.post<IContributionSaveResponse>('/dashboard/contribution', envelope);
    }
}