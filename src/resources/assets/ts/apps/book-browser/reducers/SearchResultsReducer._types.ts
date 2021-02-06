import {
    IReduxAction,
} from '@root/_types';
import {
    SearchResultGroups,
} from '@root/config';

export interface ISearchResult {
    id: number;
    groupId?: keyof typeof SearchResultGroups;
    normalizedWord: string;
    originalWord: string;
    word: string;
}

export interface ISearchResultState {
    groups: string[];
    resultIds: number[];
    resultsByGroupIndex: ISearchResult[][];
    resultsById: { [ id: number ]: ISearchResult };
    selectedId: number;
}

export type ISetSearchResultAction = Map<string, ISearchResult[]>;
export interface ISelectSearchResultAction {
    id: number;
}
export interface ISelectNextSearchResult {
    direction: number;
}
export type ISearchResultReducerAction = IReduxAction & {
    searchResults?: ISetSearchResultAction,
} & Partial<ISelectSearchResultAction> & Partial<ISelectNextSearchResult>;
