import { Actions } from '../actions';
import {
    IPostsReducerAction,
    IPostsState,
} from './PostsReducer._types';

const PostsReducer = (state: IPostsState = [], action: IPostsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveThread:
            return state.filter(p => p.forumThreadId !== action.threadData.threadId)
                .concat(action.threadData.posts.map((post) => {
                    post._isThreadPost = (post.id === action.threadData.threadPostId);
                    post._isFocused = (post.id === action.threadData.jumpPostId);
                    return post;
                }),
            );
        case Actions.UpdatePost: {
            const pos = getPostIndex(state, action);
            if (pos === -1) {
                return state;
            }

            return [
                ...state.slice(0, pos),
                action.postData.post,
                ...state.slice(pos + 1),
            ];
        }
        case Actions.DeletePost: {
            const pos = getPostIndex(state, action);
            if (pos === -1) {
                return state;
            }

            return [
                ...state.slice(0, pos),
                ...state.slice(pos + 1),
            ];
        }
        default:
            return state;
    }
};

const getPostIndex = (state: IPostsState, action: IPostsReducerAction) => {
    const postData = action.postData;
    const pos = state.findIndex((p) => p.id === postData.post.id);

    return pos;
};

export default PostsReducer;
