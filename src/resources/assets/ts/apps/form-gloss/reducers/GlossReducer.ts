import { Actions } from '../actions';
import {
    IGlossAction,
    IGlossState,
} from './GlossReducer._types';

const GlossReducer = (state: IGlossState = {
    accountId: 0,
    comments: '',
    etymology: null,
    externalId: null,
    id: 0,
    inflections: {},
    isCanon: false,
    isIndex: false,
    isLatest: false,
    isRejected: false,
    isUncertain: false,
    languageId: 0,
    originalGlossId: null,
    senseId: 0,
    source: '',
    tengwar: '',
    translations: [],
    type: '',
    word: '',
}, action: IGlossAction) => {
    switch (action.type) {
        case Actions.ReceiveGloss:
            return action.gloss;
        default:
            return state;
    }
};

export default GlossReducer;
