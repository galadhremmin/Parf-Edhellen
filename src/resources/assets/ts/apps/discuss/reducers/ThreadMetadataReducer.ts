import { Actions } from '../actions';
import {
    IThreadMetadataReducerAction,
    IThreadMetadataState,
} from './ThreadMetadataReducer._types';

const ThreadMetadataReducer = (state: IThreadMetadataState = {
    likes: [],
    likesPerPost: {},
    loading: false,
}, action: IThreadMetadataReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveThreadMetadata:
            return {
                // merge likes and likes per post.
                likes: state.likes.concat(
                    action.metadata.likes.filter(
                        (v) => state.likes.indexOf(v) === -1,
                    ),
                ),
                likesPerPost: {
                    ...state.likesPerPost,
                    ...action.metadata.likesPerPost,
                },
                loading: false,
            };
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
