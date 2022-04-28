import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../actions';
import {
    IGlossAction,
    IGlossState,
} from './GlossReducer._types';

const InitialState: IGlossState = {
    account: null,
    comments: '',
    etymology: null,
    externalId: null,
    glossDetails: [],
    glossGroupId: 0,
    id: 0,
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
};

const GlossReducer = (state: IGlossState = InitialState, action: IGlossAction) => {
    switch (action.type) {
        case Actions.ReceiveGloss:
            return mapper<typeof action['gloss'], IGlossState>({
                account: 'account',
                comments: (gloss) => gloss.comments || '',
                contributionId: 'contributionId',
                etymology: 'etymology',
                externalId: 'externalId',
                glossDetails: 'glossDetails',
                glossGroupId: 'glossGroupId',
                id: 'id',
                isRejected: 'isRejected',
                isUncertain: 'isUncertain',
                keywords: 'keywords',
                languageId: 'languageId',
                latestGlossVersionId: 'latestGlossVersionId',
                sense: 'sense',
                source: 'source',
                speechId: 'speechId',
                tengwar: (gloss) => gloss.tengwar || '',
                translations: 'translations',
                word: 'word',
            }, action.gloss);
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
