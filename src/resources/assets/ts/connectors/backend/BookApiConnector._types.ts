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
    fragments: ISentenceFragmentEntity[];
    latin: Array<[number, string?] | string>;
    tengwar: Array<[number, string?] | string>;
}

export interface ISentenceFragmentEntity {
    comments: string;
    fragment: string;
    gloss_id: number;
    id: number;
    inflections: IInflectionEntity[];
    sentence_number: number;
    speech: string;
    speech_id: number;
    tengwar: string;
    type: number;
}

export interface IInflectionEntity {
    id: number;
    name: string;
}
