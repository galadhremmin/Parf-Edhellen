import { Actions } from '../actions';
import {
    ISentenceReducerAction,
    ISentenceReducerState,
} from './SentenceReducer._types';

const SentenceReducer = (state: ISentenceReducerState = {
    account: null,
    createdAt: null,
    description: '',
    id: 0,
    isApproved: true,
    isNeologism: false,
    language: null,
    longDescription: '',
    name: '',
    source: '',
    updatedAt: null,
}, action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return action.sentence.sentence;
        default:
            return state;
    }
};

export default SentenceReducer;
