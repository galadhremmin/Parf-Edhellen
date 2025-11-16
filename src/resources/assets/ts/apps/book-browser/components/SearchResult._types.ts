import type { ComponentEventHandler } from '@root/components/Component._types';
import type { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IProps {
    searchResult: ISearchResult;
    selected?: boolean;
    onClick?: ComponentEventHandler<ISearchResult>;
}
