import { mapper } from '@root/utilities/func/mapper';
import { Actions } from '../../actions';
import {
    ISentenceTranslationAction,
    ISentenceTranslationReducerState,
} from './SentenceTranslationReducer._types';

const InitialState: ISentenceTranslationReducerState = {
    paragraphNumber: 0,
    sentenceNumber: 0,
    translation: '',
};

const SentenceTranslationReducer = (state = InitialState, action: ISentenceTranslationAction) => {
    switch (action.type) {
        case Actions.ReloadAllFragments:
        case Actions.ReceiveTranslation:
        case Actions.SetTranslation:
            return mapper<typeof action['sentenceTranslation'], ISentenceTranslationReducerState>({
                paragraphNumber: 'paragraphNumber',
                sentenceNumber: 'sentenceNumber',
                translation: 'translation',
            }, action.sentenceTranslation);
        default:
            return state;
    }
};

export default SentenceTranslationReducer;
