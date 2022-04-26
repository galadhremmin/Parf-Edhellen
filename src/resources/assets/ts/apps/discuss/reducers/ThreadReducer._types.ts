import {
    IReduxAction,
} from '@root/_types';

import {
    ICreatePostRequest,
    IPostRequest,
    IPostResponse,
    IThreadEntity,
    IThreadMetadataRequest,
    IThreadRequest,
    IThreadResponse,
} from '@root/connectors/backend/IDiscussApi';

export type ICreatePostAction = ICreatePostRequest;
export type IThreadAction = IThreadRequest;
export type IPostAction = IPostRequest;
export type IThreadMetadataAction = IThreadMetadataRequest;

export interface IChangePostAction {
    propertyName: string;
    forumThreadId: number;
    value: string;
}

export interface IThreadState extends Partial<IThreadEntity> {
    loading?: boolean;
}

export interface IThreadReducerAction extends IReduxAction {
    entityId?: number;
    entityType?: string;
    threadData: IThreadResponse;
    postData: IPostResponse;
}
