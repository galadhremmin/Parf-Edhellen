import { IGlossGroup } from './IGlossResourceApi';

export interface IFindRequest {
    includeOld?: boolean;
    languageId?: number;
    word?: string;
    reversed?: boolean;
}

export interface IFindEntity {
    k: string;
    nk: string;
    ok: string;
}

export type FindResponse = IFindEntity[];

export interface IGlossaryRequest {
    includeOld: boolean;
    inflections?: boolean;
    languageId?: number;
    normalizedWord?: string;
    word: string;
}

export interface IGlossaryResponse {
    languages: ILanguageEntity[] | null;
    sections: Array<{
        glosses: IBookGlossEntity[];
        language: ILanguageEntity;
    }>;
    sense: number[];
    single: boolean;
    word: string;
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
    comments: string; // TODO ???
    createdAt: string;
    etymology: string;
    externalId: string;
    externalLinkFormat: string;
    glossDetails: IBookGlossDetailEntity[];
    glossGroupId: number;
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
    translations: Array<{
        translation: string;
    }>;
    type: string;
    word: string;
}

export interface IBookGlossDetailEntity {
    category: string;
    order: number;
    text: string;
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
    [sentenceFragmentId: number]: ISentenceFragmentInflection[];
}

export interface ISentenceFragmentInflection {
    inflectionId: number;
    name: string;
}

export interface ISentenceEntity {
    account?: IAccountEntity;
    createdAt?: string;
    description?: string;
    id: number;
    isApproved?: boolean;
    isNeologism?: boolean;
    language?: ILanguageEntity;
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
    id: number;
    sentenceNumber: number;
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
}

export interface ITextTransformation {
    [paragraphNumber: string]: ParagraphTransformation;
}
export type ParagraphTransformation = FragmentTransformation[];
export type FragmentTransformation = [number, string?] | string;

export interface ISpeechMap {
    [speechId: string]: string;
}

export interface ISuggestRequest {
    inexact?: boolean;
    languageId?: number;
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

export default interface IBookApi {
    find(args: IFindRequest): Promise<FindResponse>;
    gloss(id: number): Promise<IGlossaryResponse>;
    glossary(args: IGlossaryRequest): Promise<IGlossaryResponse>;
    groups(): Promise<IGlossGroup[]>;
    languages(): Promise<ILanguagesResponse>;
    sentence(args: ISentenceRequest): Promise<ISentenceResponse>;
    suggest(args: ISuggestRequest): Promise<ISuggestResponse>;
}
