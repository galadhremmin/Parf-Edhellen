import { IGlossGroup } from './IGlossResourceApi';

export interface IFindRequest {
    glossGroupIds?: number[];
    includeOld?: boolean;
    languageId?: number;
    word?: string;
    reversed?: boolean;
    speechIds?: number[];
}

export interface IFindEntity {
    g: number;
    k: string;
    nk: string;
    ok: string;
}

export interface ISearchGroups {
    [groupId: number]: string;
}

export interface IFindResponse {
    keywords: IFindEntity[];
    searchGroups: ISearchGroups;
}

export interface IEntitiesRequestData {
    glossGroupIds?: number[];
    includeOld: boolean;
    inflections?: boolean;
    languageId?: number;
    normalizedWord?: string;
    speechIds?: number[];
    word: string;
}

export interface IGlossaryResponse {
    languages: ILanguageEntity[] | null;
    sections: {
        glosses: IBookGlossEntity[];
        language: ILanguageEntity;
    }[];
    sense: number[];
}

export interface ILanguagesResponse {
    [period: string]: ILanguageEntity[];
}

export interface ILanguageEntity {
    id: number;
    isInvented?: boolean;
    isUnusual?: boolean;
    name: string;
    shortName?: string;
    tengwar?: string;
    tengwarMode?: string;
}

export interface IBookGlossEntity {
    accountId: number;
    accountName: string;
    accountUrl: string;
    allTranslations: string;
    commentCount: number;
    comments: string;
    createdAt: string;
    etymology: string;
    externalId: string;
    externalLinkFormat: string;
    glossDetails: IBookGlossDetailEntity[];
    glossGroupId: number;
    glossGroupLabel?: string;
    glossGroupName: string;
    id: number;
    inflectedWord?: IBookWordInflection;
    inflections: {
        [ sentenceId: string ]: IBookInflectionEntity[];
    };
    isCanon: boolean;
    isIndex: boolean;
    isLatest: boolean;
    isRejected: boolean;
    isUncertain: boolean;
    language: ILanguageEntity;
    languageId: number;
    normalizedWord: string;
    originalGlossId: number;
    phonetic: null; // unsupported: always `null`
    rating: number;
    senseId: number;
    source: string;
    tengwar: string;
    translations: {
        translation: string;
    }[];
    type: string;
    word: string;
}

export interface IBookGlossDetailEntity {
    category: string;
    order: number;
    text: string;
    type?: string;
}

export interface IBookWordInflection {
    inflections: ISentenceFragmentInflection[];
    speech: string;
    word: string;
}

export interface IBookInflectionEntity {
    glossId: number;
    inflection: string;
    languageId: number;
    languageName: string;
    sentenceFragmentId: number;
    sentenceId: number;
    sentenceName: string;
    sentenceUrl: string;
    speech: string;
    word: string;
}

export interface ISentenceRequest {
    id: number;
}

export interface ISentenceResponse {
    inflections: ISentenceFragmentInflectionMap;
    sentence: ISentenceEntity;
    sentenceFragments: ISentenceFragmentEntity[];
    sentenceTranslations: ISentenceTranslationMap;
    sentenceTransformations: ITextTransformationsMap;
    speeches: ISpeechMap;
}

export interface ISentenceFragmentInflectionMap {
    [sentenceFragmentId: string]: ISentenceFragmentInflection[];
}

export interface ISentenceFragmentInflection {
    inflectionId: number;
    name?: string;
}

export interface ISentenceEntity {
    account?: IAccountEntity;
    createdAt?: string;
    description?: string;
    id: number;
    isApproved?: boolean;
    isNeologism?: boolean;
    language?: ILanguageEntity;
    languageId?: number;
    longDescription?: string;
    name?: string;
    source?: string;
    updatedAt?: string;
}

export interface IAccountEntity {
    avatarPath?: string;
    hasAvatar?: boolean;
    id: number;
    isConfigured?: boolean;
    nickname: string;
    profile?: string;
    tengwar?: string;
}

export interface ISentenceFragmentEntity {
    comments?: string;
    fragment?: string;
    glossId?: number;
    id?: number;
    inflections?: ISentenceFragmentInflection[];
    paragraphNumber?: number;
    sentenceNumber?: number;
    speech?: string;
    speechId?: number;
    tengwar?: string;
    type?: SentenceFragmentType;
}

export interface ISentenceTranslationEntity {
    sentenceId?: number;
    sentenceNumber: number;
    translation: string;
    paragraphNumber: number;
}

export enum SentenceFragmentType {
    Word              = 0,
    NewLine           = 10,
    Exclude           = 24,
    Interpunctuation  = 31,
    OpenParanthesis   = 40,
    CloseParanthesis  = 41,
    WordConnection    = 45,
}

export interface IBookInflectionEntity {
    id: number;
    name: string;
}

export interface ISentenceTranslationMap {
    [paragraphNumber: string]: ISentenceTranslation;
}

export interface ISentenceTranslation {
    sentenceNumber: number;
    translation: string;
}

export interface ITextTransformationsMap {
    [transformationName: string]: ITextTransformation;
    latin?: ITextTransformation;
}

export interface ITextTransformation {
    [paragraphNumber: string]: ParagraphTransformation;
}
export type ParagraphTransformation = FragmentTransformation[];
export type FragmentTransformation = [number, string?] | string;

export interface ISpeechMap {
    [speechId: string]: string;
}

export interface IEntitiesRequest {
    data: IEntitiesRequestData;
    groupId: number;
}

export interface IEntitiesResponse<T> {
    entities: T;
    groupId: number;
    single: boolean;
    word: string;
}

export default interface IBookApi {
    entities<T = IGlossaryResponse>(args: IEntitiesRequest): Promise<IEntitiesResponse<T>>;
    find(args: IFindRequest): Promise<IFindResponse>;
    gloss(id: number): Promise<IGlossaryResponse>;
    groups(): Promise<IGlossGroup[]>;
    languages(): Promise<ILanguagesResponse>;
    sentence(args: ISentenceRequest): Promise<ISentenceResponse>;
}
