import type { ThunkDispatch } from 'redux-thunk';
import type { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IProps {
    dispatch?: ThunkDispatch<any, any, any>;
    loading?: boolean;
    reversed?: boolean;
    searchGroups: string[];
    searchResults: ISearchResult[][];
    selectedResultId?: number;
    word: string;
}
