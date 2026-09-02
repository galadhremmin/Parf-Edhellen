import type { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IExpandSearchResultAction {
    lexicalEntryGroupIds?: number[];
    includeOld?: boolean;
    languageId?: number;
    searchResult: ISearchResult;
    speechIds?: number[];
    updateBrowserHistory?: boolean;
}

export interface IBrowserHistoryState {
    glossary: boolean;
    groupId: number;
    /** See `IEntitiesRequestData.inflection` — the literal inflected form searched for, if any. */
    inflection?: string;
    languageShortName: string;
    /** Set when the entry was loaded by reference rather than by search, e.g. from a word list. */
    lexicalEntryId?: number;
    normalizedWord: string;
    word: string;
}
