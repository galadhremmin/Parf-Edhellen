import {
    ISentenceEntity,
    ISentenceFragmentEntity,
    ISentenceTranslationEntity,
    ITextTransformationsMap,
} from './IBookApi';
import { IGlossEntity } from './IGlossResourceApi';

export type IContribution<T> = T & {
    contributionId?: number;
    dependentOnContributionId?: number;
}

export interface ISaveSentenceContributionEntity extends Partial<ISentenceEntity> {
    fragments: Partial<ISentenceFragmentEntity>[];
    translations: ISentenceTranslationEntity[];
}

export interface IContributionSaveResponse {
    id: number;
    url: string;
}

export interface IFragmentSuggestion {
    glossId: number;
    inflectionIds: number[];
    speechId: number;
}

export interface IFragmentSuggestions {
    [fragment: string]: IFragmentSuggestion;
}

export interface IValidateTransformationsResponse {
    suggestions?: IFragmentSuggestions;
    transformations: ITextTransformationsMap;
}

export interface IGlossContributionApi {
    saveGloss(args: IContribution<IGlossEntity>): Promise<IContributionSaveResponse>;
}

export interface ISentenceContributionApi {
    saveSentence(args: IContribution<ISaveSentenceContributionEntity>): Promise<IContributionSaveResponse>;
    validateSentenceMetadata(args: ISentenceEntity): Promise<void>;
    validateSentenceFragments(args: ISentenceFragmentEntity[]): Promise<void>;
    validateTransformations(args: ISentenceFragmentEntity[], suggestForLanguageId?: number): Promise<IValidateTransformationsResponse>;
}

export type ContributionMorph = 'gloss' | 'sentence' | 'gloss_infl';

export default interface IContributionResourceApi extends IGlossContributionApi, ISentenceContributionApi {
    saveContribution<T>(args: IContribution<T>, morph: ContributionMorph): Promise<IContributionSaveResponse>;
}
