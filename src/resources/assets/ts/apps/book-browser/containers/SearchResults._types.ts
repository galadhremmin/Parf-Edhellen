import { ThunkDispatch } from 'redux-thunk';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IProps {
    dispatch?: ThunkDispatch<any, any, any>;
    searchResults: ISearchResult[];
    word: string;
}
