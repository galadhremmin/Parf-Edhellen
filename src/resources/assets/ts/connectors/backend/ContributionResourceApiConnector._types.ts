export type IContribution<T> = T & {
    contributionId?: number;
};

export interface IContributionSaveResponse {
    id: number;
    url: string;
}
