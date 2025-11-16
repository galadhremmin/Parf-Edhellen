import { Actions } from '../actions';
import type {
    IThreadReducerAction,
    IThreadState,
} from './ThreadReducer._types';

const ThreadReducer = (state: IThreadState = {
    accountId: 0,
    createdAt: null,
    entityId: 0,
    entityType: null,
    forumGroupId: 0,
    id: 0,
    isSticky: false,
    loading: true,
    normalizedSubject: null,
    numberOfLikes: 0,
    numberOfPosts: 0,
    subject: null,
    updatedAt: null,
}, action: IThreadReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveThread:
            return {
                ...action.threadData.thread,
                loading: false,
            };
        case Actions.RequestThread:
            return {
                ...state,
                loading: true,
            };
        default:
            return state;
    }
};

export default ThreadReducer;
