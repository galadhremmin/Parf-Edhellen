import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import {
    ISentenceEntity,
    ISentenceFragmentEntity,
} from './IBookApi';
import IContributionResourceApi, {
    ContributionMorph,
    IContribution,
    IContributionSaveResponse,
    ISaveSentenceContributionEntity,
    IValidateTransformationsResponse,
} from './IContributionResourceApi';
import { ILexicalEntryEntity } from './IGlossResourceApi';

export default class ContributionResourceApiConnector implements IContributionResourceApi {
    private static readonly ApiPrefix = '/contribute/contribution';

    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public saveLexicalEntry(args: IContribution<ILexicalEntryEntity>) {
        return this.saveContribution(args, 'lexical_entry');
    }

    public saveSentence(args: IContribution<ISaveSentenceContributionEntity>): Promise<IContributionSaveResponse> {
        return this.saveContribution(args, 'sentence');
    }

    public saveContribution<T>(args: IContribution<T>, morph: ContributionMorph) {
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

    public validateTransformations(args: ISentenceFragmentEntity[], suggestForLanguageId = 0) {
        const envelope: Record<string, unknown> = {
            fragments: args,
            morph: 'sentence',
            substepId: 2,
        };

        if (suggestForLanguageId !== 0) {
            envelope.suggestForLanguageId = suggestForLanguageId;
        }

        return this._api.post<IValidateTransformationsResponse>(this._apiPath('substep-validate'), envelope);
    }

    private _apiPath(path = '') {
        return `${ContributionResourceApiConnector.ApiPrefix}/${path}`;
    }
}
