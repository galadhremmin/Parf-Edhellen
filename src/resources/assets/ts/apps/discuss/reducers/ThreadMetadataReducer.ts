import { Actions } from '../actions';
import {
    IThreadMetadataReducerAction,
    IThreadMetadataState,
} from './ThreadMetadataReducer._types';

const ThreadMetadataReducer = (state: IThreadMetadataState = {
    likes: [],
    likesPerPost: {},
    loading: false,
}, action: IThreadMetadataReducerAction): IThreadMetadataState => {
    switch (action.type) {
        case Actions.ReceiveThreadMetadata: {
            const likes = action.metadata.likes.concat(
                state.likes.filter(
                    (id) => action.metadata.forumPostId.indexOf(id) === -1,
                ),
            );

            return {
                // merge likes and likes per post.
                likes,
                likesPerPost: {
                    ...state.likesPerPost,
                    ...action.metadata.likesPerPost,
                },
                loading: false,
            };
        }
        case Actions.RequestThreadMetadata:
            return {
                ...state,
                loading: true,
            };
        default:
            return state;
    }
};

export default ThreadMetadataReducer;
