import {
    IReduxAction,
} from '../../../_types';

export interface ISearchResult {
    normalizedWord: string;
    originalWord: string;
    word: string;
    id: number;
}

export type ISearchResultState = ISearchResult[];
export type ISetSearchResultAction = ISearchResult[];
export type ISearchResultReducerAction = IReduxAction & {
    searchResults: ISearchResult[],
};
