import {
    IReduxAction,
} from '../../../_types';

export interface ISearchResult {
    id: number;
    normalizedWord: string;
    originalWord: string;
    selected: boolean;
    word: string;
}

export type ISearchResultState = ISearchResult[];
export type ISetSearchResultAction = ISearchResult[];
export interface ISelectSearchResultAction {
    id: number;
}
export type ISearchResultReducerAction = IReduxAction & {
    searchResults?: ISearchResult[],
} & ISelectSearchResultAction;
