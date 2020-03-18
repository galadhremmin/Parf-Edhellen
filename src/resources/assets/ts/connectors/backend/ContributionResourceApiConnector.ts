import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import {
    ISentenceEntity,
    ISentenceFragmentEntity,
} from './IBookApi';
import IContributionResourceApi, {
    IContribution,
    IContributionSaveResponse,
    IValidateTransformationsResponse,
} from './IContributionResourceApi';
import { IGlossEntity } from './IGlossResourceApi';

export default class ContributionResourceApiConnector implements IContributionResourceApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public saveGloss(args: IContribution<IGlossEntity>) {
        const envelope = {
            ...args,
            morph: 'gloss',
        };

        if (!!args.contributionId && ! isNaN(args.contributionId) && isFinite(args.contributionId)) {
            return this._api.put<IContributionSaveResponse>(`/dashboard/contribution/${args.contributionId}`,
                envelope);
        }

        delete envelope.contributionId;
        return this._api.post<IContributionSaveResponse>('/dashboard/contribution', envelope);
    }

    public validateSentenceMetadata(args: ISentenceEntity) {
        const envelope = {
            ...args,
            morph: 'sentence',
            substepId: 0,
        };

        return this._api.post<void>('/dashboard/contribution/substep-validate', envelope);
    }

    public validateSentenceFragments(args: ISentenceFragmentEntity[]) {
        const envelope = {
            fragments: args,
            morph: 'sentence',
            substepId: 1,
        };

        return this._api.post<void>('/dashboard/contribution/substep-validate', envelope);
    }

    public validateTransformations(args: ISentenceFragmentEntity[]) {
        const envelope = {
            fragments: args,
            morph: 'sentence',
            substepId: 2,
        };

        return this._api.post<IValidateTransformationsResponse>('/dashboard/contribution/substep-validate', envelope);
    }
}
