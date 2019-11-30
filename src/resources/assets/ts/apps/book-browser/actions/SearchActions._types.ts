import { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface ILoadGlossaryAction {
    includeOld?: boolean;
    languageId?: number;
    searchResult: ISearchResult;
    updateBrowserHistory: boolean;
}

export interface IBrowserHistoryState {
    glossary: boolean;
    languageShortName: string;
    normalizedWord: string;
    word: string;
}
