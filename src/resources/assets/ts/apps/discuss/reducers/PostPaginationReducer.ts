import {
    IPostPaginationState,
    IPostsReducerAction,
} from './PostPaginationReducer._types';
import { Actions } from '../actions';
import { mapper } from '@root/utilities/func/mapper';

const PostPaginationReducer = (state: IPostPaginationState = {
    currentPage: 0,
    noOfPages: 0,
    pages: []
}, action: IPostsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveThread:
            return mapper<typeof action.threadData, IPostPaginationState>({
                'currentPage': 'currentPage',
                'noOfPages': 'noOfPages',
                'pages': 'pages',
            }, action.threadData);
        default:
            return state;
    }
}

export default PostPaginationReducer;
