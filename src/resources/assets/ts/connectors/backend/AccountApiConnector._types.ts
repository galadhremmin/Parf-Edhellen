export interface IFindRequest {
    max?: number;
    nickname: string;
}

export type FindResponse = IAccountSuggestion[];

export interface IAccountSuggestion {
    nickname: string;
}
