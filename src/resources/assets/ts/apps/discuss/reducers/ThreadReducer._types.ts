import {
    IReduxAction,
} from '@root/_types';

import {
    ICreateRequest,
    IThreadEntity,
    IThreadRequest,
    IThreadResponse,
} from '@root/connectors/backend/DiscussApiConnector._types';

export type ICreatePostAction = ICreateRequest;
export type IThreadAction = IThreadRequest;

export interface IThreadState extends IThreadEntity {
    loading: boolean;
}

export interface IThreadReducerAction extends IReduxAction {
    threadData: IThreadResponse;
}
