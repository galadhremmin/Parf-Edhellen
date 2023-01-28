import {
    IThreadReducerAction,
} from './ThreadReducer._types';

export interface IPostPaginationState {
    currentPage: number;
    noOfPages: number;
    noOfPosts: number;
    pages: (string | number)[];
}

export type IPostsReducerAction = IThreadReducerAction;
