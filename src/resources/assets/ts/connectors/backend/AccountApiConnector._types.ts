export interface IFindRequest {
    max?: number;
    nickname: string;
}

export type FindResponse = IAccountSuggestion[];

export interface IAccountSuggestion {
    id: number;
    nickname: string;
}

export interface IGetAvatarRequest {
    accountId: number;
}

export interface IGetAvatarResponse {
    avatar: string;
}
