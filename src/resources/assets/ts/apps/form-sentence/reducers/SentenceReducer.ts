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
    language: null,
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
                language: 'language',
                longDescription: 'longDescription',
                name: 'name',
                source: 'source',
            }, action.sentence);
        default:
            return state;
    }
};

export default SentenceReducer;
