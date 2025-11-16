/* eslint @typescript-eslint/no-empty-object-type: 0 */

import type { ISentenceEntity, ISentenceFragmentEntity } from "./IBookApi";
import type { IPostEntity } from "./IDiscussApi";
import type { ILexicalEntryEntity } from "./IGlossResourceApi";

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

export interface IGetFeedRequest {
    accountId: number;
    cursor?: string;
}

export interface IGetFeedResponse {
    data: IFeedRecord[];
    path: string;
    perPage: number;
    nextCursor: string;
    nextPageUrl: string | null;
    prevPageUrl: string | null;
    restricted?: boolean;
}

export interface IFeedRecord<TContentType = string, TEntity = any> {
    id: string;
    accountId: number;
    happenedAt: string;
    contentId: number;
    auditTrailActionId: number;
    auditTrailId: number;
    contentType: TContentType;
    content: TEntity;
}

export interface IForumFeedRecord extends IFeedRecord<'forum', IPostEntity> {}
export interface ILexicalEntryVersionFeedRecord extends IFeedRecord<'lex_entry_ver', ILexicalEntryEntity & {
    lexicalEntryId?: number;
    versionChangeFlags?: number;
}> {}
export interface ISentenceFeedRecord extends IFeedRecord<'sentence', ISentenceEntity & {
    sentenceUrl: string;
    sentenceFragments: ISentenceFragmentEntity[];
    sentenceTransformations: Record<string, Record<string, Array<[number, string?] | string>>>;
}> {}

export default interface IAccountApi {
    find(args: IFindRequest): Promise<FindResponse>;
    getAvatar(args: IGetAvatarRequest): Promise<IGetAvatarResponse>;
    getFeed(args: IGetFeedRequest): Promise<IGetFeedResponse>;
    getFeatureBackgrounds(): Promise<IGetFeatureBackgroundsResponse>;
    saveAvatar(args: ISaveAvatarRequest): Promise<ISaveAvatarResponse>;
    saveFeatureBackground(args: ISaveFeatureBackgroundRequest): Promise<ISaveFeatureBackgroundResponse>;
    saveProfile(args: ISaveProfileRequest): Promise<ISaveProfileResponse>;
}
