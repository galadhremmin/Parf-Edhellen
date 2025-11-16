import type { IPostEntity } from '@root/connectors/backend/IDiscussApi';
import type { IThreadReducerAction } from './ThreadReducer._types';

export type IPostsState = IPostEntity[];
export type IPostsReducerAction = IThreadReducerAction;
