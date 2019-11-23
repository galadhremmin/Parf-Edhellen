import { IPostEntity } from '@root/connectors/backend/IDiscussApi';
import { IThreadReducerAction } from './ThreadReducer._types';

export type IPostsState = IPostEntity[];
export type IPostsReducerAction = IThreadReducerAction;
