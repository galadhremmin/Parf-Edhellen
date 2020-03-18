import {
    ISentenceEntity,
    ISentenceFragmentEntity,
    ITextTransformationsMap,
} from './IBookApi';
import { IGlossEntity } from './IGlossResourceApi';

export type IContribution<T> = T & {
    contributionId?: number;
};

export interface IContributionSaveResponse {
    id: number;
    url: string;
}

export interface IValidateTransformationsResponse {
    transformations: ITextTransformationsMap;
}

export default interface IContributionResourceApi {
    saveGloss(args: IContribution<IGlossEntity>): Promise<IContributionSaveResponse>;
    validateSentenceMetadata(args: ISentenceEntity): Promise<void>;
    validateSentenceFragments(args: ISentenceFragmentEntity[]): Promise<void>;
    validateTransformations(args: ISentenceFragmentEntity[]): Promise<IValidateTransformationsResponse>;
}
