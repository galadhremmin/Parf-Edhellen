import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import IContributionResourceApi, {
    IContribution,
    IContributionSaveResponse,
} from './IContributionResourceApi';
import { IGlossEntity } from './IGlossResourceApi';

export default class ContributionResourceApiConnector implements IContributionResourceApi {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public saveGloss(args: IContribution<IGlossEntity>) {
        const envelope = {
            ...args,
            morph: 'gloss',
        };

        if (!!args.contributionId && ! isNaN(args.contributionId) && isFinite(args.contributionId)) {
            return this._api.value.put<IContributionSaveResponse>(`/dashboard/contribution/${args.contributionId}`,
                envelope);
        }

        delete envelope.contributionId;
        return this._api.value.post<IContributionSaveResponse>('/dashboard/contribution', envelope);
    }
}
