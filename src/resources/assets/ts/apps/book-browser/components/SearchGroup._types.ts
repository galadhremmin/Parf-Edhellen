import type { ComponentEventHandler } from '@root/components/Component._types';
import type { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IProps {
    groupName: string;
    onClick: ComponentEventHandler<ISearchResult>;
    searchResults: ISearchResult[];
    selectedResultId?: number;
}
