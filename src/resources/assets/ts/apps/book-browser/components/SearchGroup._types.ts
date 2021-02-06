import { ComponentEventHandler } from '@root/components/Component._types';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';

export interface IProps {
    groupName: string;
    onClick: ComponentEventHandler<ISearchResult>;
    searchResults: ISearchResult[];
    selectedResultId?: number;
}
