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
    glossDetails: [],
    glossGroup: null,
    glossGroupId: 0,
    id: 0,
    isIndex: false,
    isLatest: false,
    isRejected: false,
    isUncertain: false,
    keywords: [],
    languageId: 0,
    sense: {
        word: {
            word: '',
        },
    },
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
            const {
                gloss,
            } = action;
            gloss.comments = gloss.comments || '';

            return gloss;
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
