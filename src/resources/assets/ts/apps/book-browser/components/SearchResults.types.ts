import {
    ISearchResult,
} from '../reducers/SearchResultsReducer.types';

export interface IProps {
    searchResult: ISearchResult;
    onClick: () => void;
}
