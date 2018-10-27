import {
    IReduxAction,
} from '../../../_types';
import {
    IFindActionRequest,
} from '../../../connectors/backend/BookApiConnector.types';

export type ISearchState = IFindActionRequest & {
    itemIndex?: number;
    loading?: boolean;
};

export type ISearchAction = IFindActionRequest;
export type ISearchReduxAction = IReduxAction & IFindActionRequest;
