import { IReduxAction } from '@root/_types';

export interface IPost {
    content: string;
    subject: string;
}

export interface INewPostState extends IPost {
    enabled: boolean;
    loading: boolean;
}

export interface INewPostAction extends IReduxAction {
    propertyName: string;
    value: string;
}
