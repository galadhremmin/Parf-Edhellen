import { SentenceFragmentType } from '@root/connectors/backend/IBookApi';
import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../../actions';
import {
    ISentenceFragmentAction,
    ISentenceFragmentReducerState,
} from './SentenceFragmentReducer._types';

const InitialState: ISentenceFragmentReducerState = {
    _error: null,
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
        case Actions.ReloadAllFragments:
        case Actions.ReceiveFragment:
        case Actions.SetFragment:
            return mapper<typeof action['sentenceFragment'], ISentenceFragmentReducerState>({
                _error: (v) => v._error || null,
                comments: 'comments',
                fragment: 'fragment',
                glossId: 'glossId',
                id: 'id',
                glossInflections: 'glossInflections',
                paragraphNumber: 'paragraphNumber',
                sentenceNumber: 'sentenceNumber',
                speechId: 'speechId',
                tengwar: 'tengwar',
                type: 'type',
            }, action.sentenceFragment);
        case Actions.SetFragmentField:
            return {
                ...state,
                [action.field]: action.value,
            }
        default:
            return state;
    }
};

export default SentenceFragmentReducer;
