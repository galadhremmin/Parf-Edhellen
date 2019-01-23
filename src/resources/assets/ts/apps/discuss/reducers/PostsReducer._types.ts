import {
    IReduxAction,
} from '@root/_types';

import {
    IPostEntity,
    IThreadRequest,
    IThreadResponse,
} from '@root/connectors/backend/DiscussApiConnector._types';

export type IThreadAction = IThreadRequest;

export type IState = IPostEntity[];

export interface IPostsReducerAction extends IReduxAction {
    threadData: IThreadResponse;
}
