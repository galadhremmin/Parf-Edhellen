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
export interface IThreadMetadataAction extends IThreadMetadataRequest, IThreadEntityAction {

};

export interface IChangePostAction extends IThreadEntityAction {
    propertyName: string;
    value: string;
}

export interface IThreadState extends Partial<IThreadEntity> {
    loading?: boolean;
}

export interface IThreadEntityAction {
    entityId: number;
    entityType: string;
}

export interface IThreadReducerAction extends IReduxAction, IThreadEntityAction {
    threadData: IThreadResponse;
    postData: IPostResponse;
}
