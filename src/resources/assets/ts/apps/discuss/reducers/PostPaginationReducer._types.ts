import { IThreadReducerAction } from './ThreadReducer._types';

export interface IPostPaginationState {
    currentPage: number;
    noOfPages: number;
    pages: Array<string | number>;
}

export type IPostsReducerAction = IThreadReducerAction;
