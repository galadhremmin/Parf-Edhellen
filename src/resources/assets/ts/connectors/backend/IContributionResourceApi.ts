import { IGlossEntity } from './IGlossResourceApi';

export type IContribution<T> = T & {
    contributionId?: number;
};

export interface IContributionSaveResponse {
    id: number;
    url: string;
}

export default interface IContributionResourceApi {
    saveGloss(args: IContribution<IGlossEntity>): Promise<IContributionSaveResponse>;
}
