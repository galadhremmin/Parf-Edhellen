import { IReduxAction } from '@root/_types';
import { ISearchGroups } from '@root/connectors/backend/IBookApi';

export interface ISearchResult {
    id: number;
    groupId?: number;
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
    groupIdMap: ISearchGroups;
}

export interface ISetSearchResultAction {
    keywords: Record<string, ISearchResult[]>;
    searchGroups: ISearchGroups;
}
export interface ISelectSearchResultAction {
    id: number;
}
export interface ISelectNextSearchResult {
    direction: number;
}
export type ISearchResultReducerAction = IReduxAction & {
    searchResults?: ISetSearchResultAction,
} & Partial<ISelectSearchResultAction> & Partial<ISelectNextSearchResult>;
