import { SentenceFragmentType } from '@root/connectors/backend/IBookApi';
import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../../actions';
import {
    ISentenceFragmentAction,
    ISentenceFragmentReducerState,
} from './SentenceFragmentReducer._types';

const InitialState: ISentenceFragmentReducerState = {
    comments: '',
    fragment: '',
    glossId: 0,
    id: 0,
    paragraphNumber: -1,
    sentenceNumber: -1,
    speechId: null,
    tengwar: '',
    type: SentenceFragmentType.Word,
};

const SentenceFragmentReducer = (state = InitialState, action: ISentenceFragmentAction) => {
    switch (action.type) {
        case Actions.ReceiveFragment:
        case Actions.SetFragment:
            return mapper<typeof action['sentenceFragment'], ISentenceFragmentReducerState>({
                comments: 'comments',
                fragment: 'fragment',
                glossId: 'glossId',
                id: 'id',
                paragraphNumber: 'paragraphNumber',
                sentenceNumber: 'sentenceNumber',
                speechId: 'speechId',
                tengwar: 'tengwar',
                type: 'type',
            }, action.sentenceFragment);
        default:
            return state;
    }
};

export default SentenceFragmentReducer;
