import {
    IReduxAction,
} from '@root/_types';

import {
    ICreateRequest,
    IThreadEntity,
    IThreadRequest,
    IThreadResponse,
} from '@root/connectors/backend/DiscussApiConnector._types';
import { IFormChangeData } from '../components/Form._types';

export type ICreatePostAction = ICreateRequest;
export type IThreadAction = IThreadRequest;

export interface IChangePostAction {
    propertyName: string;
    value: string;
}

export interface IThreadState extends IThreadEntity {
    loading: boolean;
}

export interface IThreadReducerAction extends IReduxAction {
    threadData: IThreadResponse;
}
