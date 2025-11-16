import type { IReduxAction } from '@root/_types';
import type { IFindRequest } from '@root/connectors/backend/IBookApi';

export type ISearchState = IFindRequest & {
    itemIndex?: number;
    loading?: boolean;
};

export type ISearchAction = IFindRequest;
export type ISearchReduxAction = IReduxAction & IFindRequest;
