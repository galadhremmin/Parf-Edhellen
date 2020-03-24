import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../actions';
import {
    ISentenceAction,
    ISentenceReducerState,
} from './SentenceReducer._types';

const InitialState: ISentenceReducerState = {
    account: null,
    contributionId: 0,
    description: '',
    id: 0,
    isApproved: false,
    isNeologism: true,
    longDescription: '',
    name: '',
    source: '',
};

const SentenceReducer = (state: ISentenceReducerState = InitialState, action: ISentenceAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return mapper<typeof action['sentence'], ISentenceReducerState>({
                account: 'account',
                description: (v) => v.description || '',
                contributionId: 'contributionId',
                id: 'id',
                isApproved: 'isApproved',
                isNeologism: 'isNeologism',
                languageId: (v) => v.languageId || (v.language ? v.language.id : null),
                longDescription: (v) => v.longDescription || '',
                name: (v) => v.name || '',
                source: (v) => v.source || '',
            }, action.sentence);
        case Actions.SetField:
            return {
                ...state,
                [action.field]: action.value,
            };
        default:
            return state;
    }
};

export default SentenceReducer;
