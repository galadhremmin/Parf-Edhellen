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
}, action: INewPostAction) => {
    switch (action.type) {
        case Actions.ReceiveCreatePost:
            return {
                ...state,
                enabled: false, // disable editing mode
                loading: false,
            };
        case Actions.RequestCreatePost:
            return {
                ...state,
                loading: true,
            };
        case Actions.ChangeNewPost:
            return {
                ...state,
                [action.propertyName]: action.value,
            };
        case Actions.CreateNewPost:
            return {
                ...state,
                enabled: true,
            };
        case Actions.DiscardNewPost:
            return {
                ...state,
                enabled: false,
            };
        default:
            return state;
    }
};

export default NewPostReducer;
