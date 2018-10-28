import { ThunkDispatch } from 'redux-thunk';
import { ISearchResult } from '../reducers/SearchResultsReducer.types';

export interface IProps {
    dispatch: ThunkDispatch<any, any, any>;
    searchResults: ISearchResult[];
}
