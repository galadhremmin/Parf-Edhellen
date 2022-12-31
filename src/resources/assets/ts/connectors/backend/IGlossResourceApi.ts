import { ISpeechEntity } from './ISpeechResourceApi';

export interface IAccountEntity {
    avatarPath?: string;
    hasAvatar?: boolean;
    id: number;
    isConfigured?: boolean;
    nickname: string;
    profile?: string;
    tengwar?: string;
}

export interface IGlossEntity {
    account?: IAccountEntity;
    accountId?: number;
    childGlossId?: number;
    comments?: string;
    createdAt?: string;
    etymology?: null;
    externalId?: string;
    glossDetails?: IGlossDetail[];
    glossGroup?: IGlossGroup;
    glossGroupId?: number;
    hasDetails?: boolean;
    id?: number;
    isDeleted?: boolean;
    isLatest?: boolean;
    isRejected?: boolean;
    isUncertain?: boolean;
    keywords?: IKeywordEntity[];
    label?: string;
    languageId: number;
    latestGlossVersionId?: number;
    originGlossId?: number;
    sense: ISenseEntity;
    senseId?: number;
    source: string;
    speech?: ISpeechEntity;
    speechId: number;
    tengwar?: string;
    translations: ITranslationEntity[];
    updatedAt?: string;
    word: IWordEntity;
}

export interface IGlossDetail {
    category: string;
    order: number;
    text: string;
    type?: string;
}

export interface IGlossGroup {
    createdAt: string;
    externalLinkFormat?: string;
    id: number;
    isCanon?: boolean;
    isOld?: boolean;
    name?: string;
    updatedAt: string;
}

export interface IKeywordEntity {
    id?: number;
    word: string;
}

export interface ISenseEntity {
    createdAt?: string;
    description?: string;
    id?: number;
    updatedAt?: string;
    word: IWordEntity;
}

export interface ITranslationEntity {
    translation: string;
}

export interface IWordEntity {
    accountId?: number;
    createdAt?: string;
    id?: number;
    normalizedWord?: string;
    reversedNormalizedWord?: string;
    updatedAt?: string;
    word: string;
}

export interface IGetGlossResponse {
    gloss: IGlossEntity;
}

export interface ISuggestRequest {
    inexact?: boolean;
    languageId?: number;
    parameterized?: boolean;
    words: string[];
}

export interface ISuggestResponse {
    [word: string]: ISuggestionEntity[];
}

export interface ISuggestionEntity {
    accountName: string;
    comments: string;
    glossGroupName: string;
    id: number;
    normalizedWord: string;
    source: string;
    translation: string;
    type: string;
    word: string;
}

export default interface IGlossResourceApi {
    delete(glossId: number, replacementId: number): Promise<void>;
    gloss(glossId: number): Promise<IGlossEntity>;
    suggest(args: ISuggestRequest): Promise<Map<string, ISuggestionEntity[]>>;
}
