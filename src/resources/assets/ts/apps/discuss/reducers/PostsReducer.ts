import { Actions } from '../actions';
import {
    IPostsReducerAction,
    IState,
} from './PostsReducer._types';

const PostsReducer = (state: IState = [], action: IPostsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveThread:
            return [];
        case Actions.ReceiveThread:
            return [ ...action.threadData.posts ];
        default:
            return state;
    }
};

export default PostsReducer;
