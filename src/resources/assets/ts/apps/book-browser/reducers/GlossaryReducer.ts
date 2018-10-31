import { Actions } from './constants';
import {
    IGlossaryAction,
    IGlossaryState,
} from './GlossaryReducer._types';

const GlossaryReducer = (state: IGlossaryState = {
    loading: false,
    single: false,
    word: null,
}, action: IGlossaryAction) => {
    switch (action.type) {
        case Actions.RequestGlossary:
            return {
                ...state,
                loading: true,
            };
        case Actions.ReceiveGlossary:
            return {
                ...state,
                loading: false,
                single: action.glossary.single,
                word: action.glossary.word,
            };
        default:
            return state;
    }
};

export default GlossaryReducer;
