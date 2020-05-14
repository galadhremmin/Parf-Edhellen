import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import {
    ISentenceEntity,
    ISentenceFragmentEntity,
} from './IBookApi';
import IContributionResourceApi, {
    IContribution,
    IContributionSaveResponse,
    ISaveSentenceContributionEntity,
    IValidateTransformationsResponse,
} from './IContributionResourceApi';
import { IGlossEntity } from './IGlossResourceApi';

export default class ContributionResourceApiConnector implements IContributionResourceApi {
    private static readonly ApiPrefix = '/dashboard/contribution';

    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public saveGloss(args: IContribution<IGlossEntity>) {
        return this.saveContribution(args, 'gloss');
    }

    public saveSentence(args: IContribution<ISaveSentenceContributionEntity>): Promise<IContributionSaveResponse> {
        return this.saveContribution(args, 'sentence');
    }

    public saveContribution<T>(args: IContribution<T>, morph: 'gloss' | 'sentence') {
        const envelope = {
            ...args,
            morph,
        };

        if (!!args.contributionId && ! isNaN(args.contributionId) && isFinite(args.contributionId)) {
            return this._api.put<IContributionSaveResponse>(this._apiPath(args.contributionId.toString(10)),
                envelope);
        }

        delete envelope.contributionId;
        return this._api.post<IContributionSaveResponse>(this._apiPath(), envelope);
    }

    public validateSentenceMetadata(args: ISentenceEntity) {
        const envelope = {
            ...args,
            morph: 'sentence',
            substepId: 0,
        };

        return this._api.post<void>(this._apiPath('substep-validate'), envelope);
    }

    public validateSentenceFragments(args: ISentenceFragmentEntity[]) {
        const envelope = {
            fragments: args,
            morph: 'sentence',
            substepId: 1,
        };

        return this._api.post<void>(this._apiPath('substep-validate'), envelope);
    }

    public validateTransformations(args: ISentenceFragmentEntity[], suggestForLanguageId: number = 0) {
        const envelope: any = {
            fragments: args,
            morph: 'sentence',
            substepId: 2,
        };

        if (suggestForLanguageId !== 0) {
            envelope.suggestForLanguageId = suggestForLanguageId;
        }

        return this._api.post<IValidateTransformationsResponse>(this._apiPath('substep-validate'), envelope);
    }

    private _apiPath(path: string = '') {
        return `${ContributionResourceApiConnector.ApiPrefix}/${path}`;
    }
}
