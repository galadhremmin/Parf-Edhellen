import { IThreadReducerAction } from './ThreadReducer._types';

export interface IPostPaginationState {
    currentPage: number;
    noOfPages: number;
    pages: (string | number)[];
}

export type IPostsReducerAction = IThreadReducerAction;
