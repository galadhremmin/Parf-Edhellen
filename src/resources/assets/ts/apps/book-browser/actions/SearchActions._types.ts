import { ISearchResult } from '../reducers/SearchResultsReducer._types';

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
    languageShortName: string;
    normalizedWord: string;
    word: string;
}
