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
            return action.threadData.posts.map((post) => {
                post._isThreadPost = (post.id === action.threadData.threadPostId);
                post._isFocused = (post.id === action.threadData.jumpPostId);
                return post;
            });
        case Actions.ReceivePost: {
            const postData = action.postData;
            const pos = state.findIndex((p) => p.id === postData.post.id);

            if (pos === -1) {
                return state;
            }

            return [
                ...state.slice(0, pos),
                postData.post,
                ...state.slice(pos + 1),
            ];
        }
        default:
            return state;
    }
};

export default PostsReducer;
