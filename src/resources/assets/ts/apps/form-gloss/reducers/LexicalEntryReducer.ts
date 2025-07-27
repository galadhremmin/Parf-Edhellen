import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../actions';
import {
    ILexicalEntryAction,
    ILexicalEntryState,
} from './LexicalEntryReducer._types';

const InitialState: ILexicalEntryState = {
    account: null,
    comments: '',
    etymology: null,
    externalId: null,
    lexicalEntryDetails: [],
    lexicalEntryGroupId: 0,
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
    glosses: [],
    word: {
        word: '',
    },
};

const LexicalEntryReducer = (state: ILexicalEntryState = InitialState, action: ILexicalEntryAction) => {
    switch (action.type) {
        case Actions.ReceiveLexicalEntry:
            return mapper<typeof action['lexicalEntry'], ILexicalEntryState>({
                account: 'account',
                comments: (entry) => entry.comments || '',
                contributionId: 'contributionId',
                etymology: 'etymology',
                externalId: 'externalId',
                lexicalEntryDetails: 'lexicalEntryDetails',
                lexicalEntryGroupId: 'lexicalEntryGroupId',
                id: 'id',
                isRejected: 'isRejected',
                isUncertain: 'isUncertain',
                keywords: 'keywords',
                languageId: 'languageId',
                latestLexicalEntryVersionId: 'latestLexicalEntryVersionId',
                sense: 'sense',
                source: 'source',
                speechId: 'speechId',
                tengwar: (entry) => entry.tengwar || '',
                glosses: 'glosses',
                word: 'word',
            }, action.lexicalEntry);
        case Actions.SetLexicalEntryField:
            return {
                ...state,
                [action.field]: action.value,
            };
        default:
            return state;
    }
};

export default LexicalEntryReducer;
