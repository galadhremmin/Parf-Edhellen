import { ThunkDispatch } from 'redux-thunk';

import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import BrowserHistory from '@root/utilities/BrowserHistory';
import SharedReference from '@root/utilities/SharedReference';

import { IThreadAction } from '../reducers/ThreadReducer._types';
import Actions from './Actions';

export default class DiscussActions {
    constructor(private _api = new SharedReference(DiscussApiConnector)) {
    }

    public thread(args: IThreadAction) {
        return async (dispatch: ThunkDispatch<any, any, any>) => {
            dispatch({
                type: Actions.RequestThread,
            });

            BrowserHistory.default.push(`?offset=${args.offset}`);

            const thread = await this._api.value.thread(args);
            dispatch({
                threadData: thread,
                type: Actions.ReceiveThread,
            });
        };
    }
}
