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

export interface IGetFeatureBackgroundsResponse {
    path: string;
    files: string[];
}

export interface ISaveAvatarRequest {
    accountId: number;
    file: File;
}

export interface ISaveAvatarResponse {
    accountId: number;
    avatarPath: string;
}

export interface ISaveFeatureBackgroundRequest {
    accountId: number;
    featureBackgroundUrl: string;
}

export interface ISaveFeatureBackgroundResponse {
    accountId: number;
    featureBackgroundUrl: string;
}

export interface ISaveProfileRequest {
    accountId: number;
    introduction: string;
    nickname: string;
    tengwar: string;
}

export interface ISaveProfileResponse {
    nickname: string;
    profileUrl: string;
}

export default interface IAccountApi {
    find(args: IFindRequest): Promise<FindResponse>;
    getAvatar(args: IGetAvatarRequest): Promise<IGetAvatarResponse>;
    getFeatureBackgrounds(): Promise<IGetFeatureBackgroundsResponse>;
    saveAvatar(args: ISaveAvatarRequest): Promise<ISaveAvatarResponse>;
    saveFeatureBackground(args: ISaveFeatureBackgroundRequest): Promise<ISaveFeatureBackgroundResponse>;
    saveProfile(args: ISaveProfileRequest): Promise<ISaveProfileResponse>;
}
