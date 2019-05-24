import { Actions } from '../actions';
import {
    IGlossAction,
    IGlossState,
} from './GlossReducer._types';

const GlossReducer = (state: IGlossState = {
    accountId: 0,
    comments: '',
    details: [],
    etymology: null,
    externalId: null,
    id: 0,
    isIndex: false,
    isLatest: false,
    isRejected: false,
    isUncertain: false,
    keywords: [],
    languageId: 0,
    sense: null,
    source: '',
    speechId: 0,
    tengwar: '',
    translations: [],
    word: {
        word: '',
    },
}, action: IGlossAction) => {
    switch (action.type) {
        case Actions.ReceiveGloss:
            return action.gloss;
        case Actions.SetField:
            return {
                ...state,
                [action.field]: action.value,
            };
        default:
            return state;
    }
};

export default GlossReducer;
