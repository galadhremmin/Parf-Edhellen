import type { IReduxAction } from '@root/_types';
import type { IThreadEntityAction } from './ThreadReducer._types';

export interface IPost {
    content: string;
    subject: string;
}

export interface INewPostState extends IPost {
    enabled: boolean;
    loading: boolean;
}

export interface INewPostAction extends IReduxAction, IThreadEntityAction {
    propertyName: string;
    value: string;
}
