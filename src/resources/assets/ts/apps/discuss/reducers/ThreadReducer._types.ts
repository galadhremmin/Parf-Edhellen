import {
    IReduxAction,
} from '@root/_types';

import {
    ICreateRequest,
    IPostRequest,
    IPostResponse,
    IThreadEntity,
    IThreadMetadataRequest,
    IThreadRequest,
    IThreadResponse,
} from '@root/connectors/backend/DiscussApiConnector._types';

export type ICreatePostAction = ICreateRequest;
export type IThreadAction = IThreadRequest;
export type IPostAction = IPostRequest;
export type IThreadMetadataAction = IThreadMetadataRequest;

export interface IChangePostAction {
    propertyName: string;
    value: string;
}

export interface IThreadState extends IThreadEntity {
    loading: boolean;
}

export interface IThreadReducerAction extends IReduxAction {
    threadData: IThreadResponse;
    postData: IPostResponse;
}
