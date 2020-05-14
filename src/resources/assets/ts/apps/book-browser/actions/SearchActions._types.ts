import { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface ILoadGlossaryAction {
    glossGroupIds?: number[];
    includeOld?: boolean;
    languageId?: number;
    searchResult: ISearchResult;
    speechIds?: number[];
    updateBrowserHistory: boolean;
}

export interface IBrowserHistoryState {
    glossary: boolean;
    languageShortName: string;
    normalizedWord: string;
    word: string;
}
