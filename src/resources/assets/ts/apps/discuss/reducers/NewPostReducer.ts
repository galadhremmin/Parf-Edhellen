import { Actions } from '../actions';
import {
    INewPostAction,
    INewPostState,
} from './NewPostReducer._types';

const NewPostReducer = (state: INewPostState = {
    content: '',
    enabled: false,
    loading: false,
    subject: '',
    forumThreadId: 0,
}, action: INewPostAction) => {
    switch (action.type) {
        case Actions.ReceiveCreatePost:
            return {
                ...state,
                enabled: false, // disable editing mode
                loading: false,
                forumThreadId: action.forumThreadId,
            };
        case Actions.RequestCreatePost:
            return {
                ...state,
                content: '',
                loading: true,
                subject: '',
                forumThreadId: action.forumThreadId,
            };
        case Actions.ChangeNewPost:
            return {
                ...state,
                forumThreadId: action.forumThreadId,
                [action.propertyName]: action.value,
            };
        case Actions.CreateNewPost:
            return {
                ...state,
                enabled: true,
                forumThreadId: action.forumThreadId,
            };
        case Actions.DiscardNewPost:
            return {
                ...state,
                enabled: false,
                forumThreadId: action.forumThreadId,
            };
        default:
            return state;
    }
};

export default NewPostReducer;
