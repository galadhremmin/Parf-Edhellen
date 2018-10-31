import {
    IReduxAction,
} from '../../../_types';
import {
    IFindRequest,
} from '../../../connectors/backend/BookApiConnector._types';

export type ISearchState = IFindRequest & {
    itemIndex?: number;
    loading?: boolean;
};

export type ISearchAction = IFindRequest;
export type ISearchReduxAction = IReduxAction & IFindRequest;
