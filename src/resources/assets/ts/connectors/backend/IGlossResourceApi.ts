import type { ISpeechEntity } from './ISpeechResourceApi';

export interface IAccountEntity {
    avatarPath?: string;
    hasAvatar?: boolean;
    id: number;
    isConfigured?: boolean;
    nickname: string;
    profile?: string;
    tengwar?: string;
    featureBackgroundUrl?: string;
}

export interface ILexicalEntryEntity {
    account?: IAccountEntity;
    accountId?: number;
    childLexicalEntryId?: number;
    comments?: string;
    createdAt?: string;
    etymology?: null;
    externalId?: string;
    lexicalEntryDetails?: ILexicalEntryDetail[];
    lexicalEntryGroup?: ILexicalEntryGroup;
    lexicalEntryGroupId?: number;
    hasDetails?: boolean;
    id?: number;
    isDeleted?: boolean;
    isLatest?: boolean;
    isRejected?: boolean;
    isUncertain?: boolean;
    keywords?: IKeywordEntity[];
    label?: string;
    languageId: number;
    latestLexicalEntryVersionId?: number;
    originLexicalEntryId?: number;
    sense: ISenseEntity;
    senseId?: number;
    source: string;
    speech?: ISpeechEntity;
    speechId: number;
    tengwar?: string;
    glosses: IGlossEntity[];
    updatedAt?: string;
    word: IWordEntity;
}

export interface ILexicalEntryDetail {
    category: string;
    order: number;
    text: string;
    type?: string;
}

export interface ILexicalEntryGroup {
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

export interface IGlossEntity {
    translation: string;
}

export interface IWordEntity {
    accountId?: number;
    createdAt?: string;
    id?: number;
    normalizedWord?: string;
    updatedAt?: string;
    word: string;
}

export interface IGetLexicalEntryResponse {
    lexicalEntry: ILexicalEntryEntity;
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
    lexicalEntryGroupName: string;
    id: number;
    normalizedWord: string;
    source: string;
    gloss: string;
    type: string;
    word: string;
}

export default interface ILexicalEntryResourceApi {
    delete(lexicalEntryId: number, replacementId: number): Promise<void>;
    lexicalEntry(lexicalEntryId: number): Promise<ILexicalEntryEntity>;
    suggest(args: ISuggestRequest): Promise<Map<string, ISuggestionEntity[]>>;
}
