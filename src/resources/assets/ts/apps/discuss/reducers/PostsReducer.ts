import { Actions } from '../actions';
import {
    IPostsReducerAction,
    IPostsState,
} from './PostsReducer._types';

const PostsReducer = (state: IPostsState = [], action: IPostsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveThread: {
            // `threadId` is not always present on the `threadData` field. For example, when
            // you preload the thread, this field is missing but still available via the
            // the `thread` field. This is a discrepancy between the server side rendering code
            // and the public API. Instead of updating the interface (which is obviously painful)
            // this hack is here for the time being. TODO: replace `threadId` with `thread`.
            // <hack>
            const threadId = action.threadData.threadId || action.threadData.thread?.id;
            // </hack>
            return state.filter(p => p.forumThreadId !== threadId) // there can be posts from multiple threads in the same reducer
                .concat(action.threadData.posts.map((post) => {
                    post._isThreadPost = (post.id === action.threadData.threadPostId);
                    post._isFocused = (post.id === action.threadData.jumpPostId);
                    return post;
                }),
            );
        }
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
