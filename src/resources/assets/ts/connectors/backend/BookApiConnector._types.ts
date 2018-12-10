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
        glosses: IGlossEntity[];
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

export interface IWordEntity {
    word: string;
    normalizedWord: string;
}

export interface IGlossEntity {
    accountId: number;
    accountName: string;
    accountUrl: string;
    allTranslations: string;
    commentCount: number;
    comments: null; // TODO ???
    createdAt: string;
    etymology: string;
    externalId: string;
    externalLinkFormat: string;
    glossDetails: IGlossDetailEntity[];
    glossGroupId: number;
    glossGroupName: string;
    id: number;
    inflections: {
        [ sentenceId: string ]: IInflectionEntity[];
    };
    isCanon: boolean;
    isIndex: boolean;
    isLatest: boolean;
    isRejected: boolean;
    isUncertain: string;
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

export interface IGlossDetailEntity {
    category: string;
    order: number;
    text: string;
}

export interface IInflectionEntity {
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
    sentence: ISentenceEntity;
    sentenceFragments: ISentenceFragmentEntity[];
    sentenceTranslations: ISentenceTranslationMap;
    sentenceTransformations: ITextTransformationsMap;
    speeches: ISpeechMap;
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
    hasAvatar?: boolean;
    id: number;
    nickname: string;
    tengwar?: string;
}

export interface ISentenceFragmentEntity {
    comments?: string;
    fragment?: string;
    glossId?: number;
    id: number;
    inflections?: IInflectionEntity[];
    sentenceNumber: number;
    speech?: string;
    speechId?: number;
    tengwar?: string;
    type?: SentenceFragmentType;
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

export interface IInflectionEntity {
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
