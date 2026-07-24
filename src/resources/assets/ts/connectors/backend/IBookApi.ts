import type {
    IAccountEntity,
    ILexicalEntryGroup,
} from './IGlossResourceApi';
import type { IInflection } from './IInflectionResourceApi';
import type { ISpeechEntity } from './ISpeechResourceApi';

export interface IFindRequest {
    lexicalEntryGroupIds?: number[];
    includeOld?: boolean;
    languageId?: number;
    speechIds?: number[];
    word?: string;
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
    lexicalEntryGroupIds?: number[];
    includeOld: boolean;
    /** The literal inflected/matched keyword the user actually searched for, when it differs from
     * `word` (the entry's real headword) — e.g. searching "lond-" navigates here with `word: "lon(de)"`
     * and `inflection: "lond-"`. Not consumed by the backend search itself; carried through purely so
     * the UI can (in the future) indicate "you searched for the inflected form X". */
    inflection?: string;
    inflections?: boolean;
    languageId?: number;
    normalizedWord?: string;
    speechIds?: number[];
    word: string;
}

export interface IGlossaryResponse<T = ILexicalEntryEntity> {
    languages: ILanguageEntity[] | null;
    /** True when the best overall match lives in an "unusual" language and no normal language has a direct
     * match of its own — in that case the view should lead with the unusual section instead of the usual
     * normal-first order. */
    leadWithUnusual?: boolean;
    sections: {
        entities: T[];
        language: ILanguageEntity;
    }[];
    sense: number[];
    single?: boolean;
}

export interface ILanguagesResponse {
    [period: string]: ILanguageEntity[];
}

export interface ILanguageEntity {
    category?: string;
    id: number;
    isInvented?: boolean;
    isUnusual?: boolean;
    name: string;
    shortName?: string;
    tengwar?: string;
    tengwarMode?: string;
}

export interface ILexicalEntryEntity {
    _inflectedWord?: IBookWordInflection;
    accountId: number;
    accountName: string;
    accountUrl: string;
    allGlosses: string;
    commentCount: number;
    comments: string;
    createdAt: string;
    derivations: IDerivationEntity[][];
    derivatives: IDerivativesTree;
    etymology: string;
    externalId: string;
    externalLinkFormat: string;
    lexicalEntryDetails: ILexicalEntryDetailEntity[];
    lexicalEntryGroupId: number;
    lexicalEntryGroupLabel?: string;
    lexicalEntryGroupName: string;
    id: number;
    inflections: {
        [ inflectionGroupUuid: string ]: IBookInflectionEntity[];
    };
    isCanon: boolean;
    isOld: boolean;
    isLatest: boolean;
    isRejected: boolean;
    isUncertain: boolean;
    label: string;
    language: ILanguageEntity;
    languageId: number;
    latestLexicalEntryVersionId: number;
    normalizedWord: string;
    originalLexicalEntryId: number;
    phoneticDevelopments: IPhoneticDevelopmentEntity[][];
    rating: number;
    senseId: number;
    source: string;
    tengwar: string;
    translations: {
        translation: string;
    }[];
    type: string;
    word: string;
    updatedAt: string;
}

export interface ILexicalEntryDetailEntity {
    category: string;
    order: number;
    text: string;
    type?: string;
}

/**
 * One step of an ancestry hypothesis, ordered from the immediate parent towards the root.
 * `source`/`comment`/`intermediateStages` only carry a value on the immediate-parent (order 0)
 * step — see LexicalEntryDerivationRepository on the backend.
 */
export interface IDerivationEntity {
    comment: string | null;
    groupUuid: string;
    intermediateStages: string[] | null;
    isRejected: boolean;
    isUncertain: boolean;
    order: number;
    parentForm: string;
    parentGloss: string | null;
    parentLabel: string | null;
    parentLanguageId: number | null;
    parentLexicalEntryId: number | null;
    parentUrl: string | null;
    parentWord: string | null;
    source: string | null;
}

/** One step of a phonetic development chain, ordered from the earliest form to the modern word. */
export interface IPhoneticDevelopmentEntity {
    groupUuid: string;
    order: number;
    previousWord: string | null;
    rule: string | null;
    word: string;
}

/**
 * One node in the "words derived from this entry" tree — either a resolved dictionary entry
 * (isWord true, lexicalEntryId/url set) or an unresolved intermediate form known only by its
 * recorded spelling. Only populated for the single-entry view (see BookAdapter::adaptDerivatives()).
 */
export interface IDerivativeNode {
    children: IDerivativeNode[];
    form: string;
    gloss: string | null;
    isWord: boolean;
    languageId: number | null;
    lexicalEntryId: number | null;
    source: string | null;
    url: string | null;
    word: string | null;
}

export interface IDerivativesTree {
    children: IDerivativeNode[];
    /** True when the tree was capped — this root has more descendants than are shown. */
    truncated: boolean;
}

export interface IBookWordInflection {
    inflections: ILexicalEntryInflection[];
    speech: string;
    word: string;
}

export interface IBookInflectionEntity {
    inflectionGroupUuid: string;
    lexicalEntryId: number;
    languageId: number;
    inflectionId: number;
    speechId: number;
    accountId: number;
    sentenceId?: number;
    sentenceFragmentId?: number;
    isNeologism?: boolean;
    isRejected?: boolean;
    source?: string;
    word: string;
    order: number;
    sentence?: ISentenceEntity;
    speech?: ISpeechEntity;
    inflection: IInflection;
    sentenceUrl?: string;
}

export interface ISentenceRequest {
    id: number;
}

export interface ISentenceResponse {
    sentence: ISentenceEntity;
    sentenceFragments: ISentenceFragmentEntity[];
    sentenceTranslations: ISentenceTranslation[];
    sentenceTransformations: ITextTransformationsMap;
    speeches: ISpeechMap;
    inflections: IInflectionMap;
}

export interface IInflectionMap {
    [inflectionId: string]: IInflection;
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
    linkHref?: string;
    longDescription?: string;
    name?: string;
    source?: string;
    updatedAt?: string;
}

export interface ISentenceFragmentEntity {
    comments?: string;
    fragment?: string;
    lexicalEntryId?: number;
    id?: number;
    inflectionGroupUuid?: string;
    lexicalEntryInflections?: ILexicalEntryInflection[];
    paragraphNumber?: number;
    sentenceNumber?: number;
    speech?: string;
    speechId?: number;
    tengwar?: string;
    type?: SentenceFragmentType;
}

export interface ILexicalEntryInflection {
    inflectionGroupUuid?: string;
    inflectionId: number;
    inflection?: IInflection;
    order?: number;
    lexicalEntryId?: number;
    languageId?: number;
    speechId?: number;
    isNeologism?: boolean;
    isRejected?: boolean;
    source?: string;
    sentenceFragmentId?: number;
    sentence?: ISentenceEntity;
    word?: string;
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

export interface ISentenceTranslation {
    paragraphNumber: number;
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
    [speechId: string]: ISpeechEntity;
}

export interface IEntitiesRequest {
    data: IEntitiesRequestData;
    groupId: number;
}

export interface IEntitiesResponse<T> {
    entities: T;
    entityMorph?: string;
    groupId: number;
    groupIntlName?: string;
    single: boolean;
    word: string;
}

export interface ISpecificEntityRequest<T> {
    groupId: number;
    entityId: number;
}

export interface IPromoteFeaturedEntryRequest {
    languageId: number;
    lexicalEntryId: number;
    previousLexicalEntryId?: number;
    searchWord: string;
}

export default interface IBookApi {
    entities<T = IGlossaryResponse>(args: IEntitiesRequest): Promise<IEntitiesResponse<T>>;
    entity<T = IGlossaryResponse>(args: ISpecificEntityRequest<T>): Promise<IEntitiesResponse<T>>;
    find(args: IFindRequest): Promise<IFindResponse>;
    lexicalEntry(id: number): Promise<IGlossaryResponse>;
    lexicalEntryFromVersion(id: number): Promise<IGlossaryResponse>;
    groups(): Promise<ILexicalEntryGroup[]>;
    languages(): Promise<ILanguagesResponse>;
    sentence(args: ISentenceRequest): Promise<ISentenceResponse>;

    /**
     * Records that the user promoted a different lexical entry to the featured (best-match)
     * slot for a search word and language. Analytics-only — does not affect what any user sees.
     */
    promoteFeaturedEntry(args: IPromoteFeaturedEntryRequest): Promise<void>;
}
