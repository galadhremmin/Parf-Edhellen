import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../actions';
import {
    IPostPaginationState,
    IPostsReducerAction,
} from './PostPaginationReducer._types';

const PostPaginationReducer = (state: IPostPaginationState = {
    currentPage: 0,
    noOfPages: 0,
    noOfPosts: 0,
    pages: [],
}, action: IPostsReducerAction): IPostPaginationState => {
    switch (action.type) {
        case Actions.ReceiveThread:
            return mapper<typeof action.threadData, IPostPaginationState>({
                currentPage: (v) => Math.max(1, v.currentPage),
                noOfPages: 'noOfPages',
                noOfPosts: 'noOfPosts',
                pages: 'pages',
            }, action.threadData);
        default:
            return state;
    }
};

export default PostPaginationReducer;
