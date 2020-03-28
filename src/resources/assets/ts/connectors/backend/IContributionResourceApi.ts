import {
    ISentenceEntity,
    ISentenceFragmentEntity,
    ISentenceTranslationEntity,
    ITextTransformationsMap,
} from './IBookApi';
import { IGlossEntity } from './IGlossResourceApi';

export type IContribution<T> = T & {
    contributionId?: number;
}

export interface ISaveSentenceContributionEntity extends Partial<ISentenceEntity> {
    fragments: Partial<ISentenceFragmentEntity>[];
    translations: ISentenceTranslationEntity[];
}

export interface IContributionSaveResponse {
    id: number;
    url: string;
}

export interface IValidateTransformationsResponse {
    transformations: ITextTransformationsMap;
}

export interface IGlossContributionApi {
    saveGloss(args: IContribution<IGlossEntity>): Promise<IContributionSaveResponse>;
}

export interface ISentenceContributionApi {
    saveSentence(args: IContribution<ISaveSentenceContributionEntity>): Promise<IContributionSaveResponse>;
    validateSentenceMetadata(args: ISentenceEntity): Promise<void>;
    validateSentenceFragments(args: ISentenceFragmentEntity[]): Promise<void>;
    validateTransformations(args: ISentenceFragmentEntity[]): Promise<IValidateTransformationsResponse>;
}

export default interface IContributionResourceApi extends IGlossContributionApi, ISentenceContributionApi {
    saveContribution<T>(args: IContribution<T>, morph: 'gloss' | 'sentence'): Promise<IContributionSaveResponse>;
}
