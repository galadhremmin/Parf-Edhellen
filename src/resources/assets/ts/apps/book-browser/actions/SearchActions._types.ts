import type { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IExpandSearchResultAction {
    lexicalEntryGroupIds?: number[];
    includeOld?: boolean;
    languageId?: number;
    naturalLanguage?: boolean;
    searchResult: ISearchResult;
    speechIds?: number[];
    updateBrowserHistory?: boolean;
}

export interface IBrowserHistoryState {
    glossary: boolean;
    groupId: number;
    languageShortName: string;
    naturalLanguage: boolean;
    normalizedWord: string;
    word: string;
}
