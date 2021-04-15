import { ISearchGroups } from '@root/connectors/backend/IBookApi';
import { ThunkDispatch } from 'redux-thunk';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IProps {
    dispatch?: ThunkDispatch<any, any, any>;
    loading: boolean;
    searchGroups: string[];
    searchResults: ISearchResult[][];
    selectedResultId: number;
    word: string;
}
