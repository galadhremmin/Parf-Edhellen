import { ComponentEventHandler } from '../../../components/Component._types';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IProps {
    searchResult: ISearchResult;
    onClick?: ComponentEventHandler<ISearchResult>;
}
