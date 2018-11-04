import { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface ILoadGlossaryAction {
    includeOld?: boolean;
    languageId?: number;
    searchResult: ISearchResult;
    updateBrowserHistory: boolean;
}
