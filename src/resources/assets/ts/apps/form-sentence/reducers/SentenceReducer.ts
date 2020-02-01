import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../actions';
import {
    ISentenceAction,
    ISentenceReducerState,
} from './SentenceReducer._types';

const InitialState: ISentenceReducerState = {
    account: null,
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
                description: 'description',
                id: 'id',
                isApproved: 'isApproved',
                isNeologism: 'isNeologism',
                languageId: (v) => v.languageId || (v.language ? v.language.id : null),
                longDescription: 'longDescription',
                name: 'name',
                source: 'source',
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
