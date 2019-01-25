import { Actions } from '../actions';
import {
    IPostsReducerAction,
    IPostsState,
} from './PostsReducer._types';

const PostsReducer = (state: IPostsState = [], action: IPostsReducerAction) => {
    switch (action.type) {
        case Actions.RequestThread:
            return [];
        case Actions.ReceiveThread:
            return [
                ...action.threadData.posts,
            ];
        default:
            return state;
    }
};

export default PostsReducer;
