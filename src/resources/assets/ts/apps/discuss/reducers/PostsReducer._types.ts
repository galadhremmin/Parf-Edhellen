import { IPostEntity } from '@root/connectors/backend/DiscussApiConnector._types';
import { IThreadReducerAction } from './ThreadReducer._types';

export type IPostsState = IPostEntity[];
export type IPostsReducerAction = IThreadReducerAction;
