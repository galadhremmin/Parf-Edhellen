import { Actions } from '../actions';
import SentenceTranslationReducer from './child-reducers/SentenceTranslationReducer';
import type {
    ISentenceTranslationsAction,
    ISentenceTranslationsReducerState,
} from './SentenceTranslationsReducer._types';

const InitialState: ISentenceTranslationsReducerState = [];

const SentenceTranslationsReducer = (state = InitialState, action: ISentenceTranslationsAction) => {
    switch (action.type) {
        case Actions.ReloadAllFragments:
        case Actions.ReceiveTranslation: {
            const newState = action.sentenceTranslations.map(
                (sentenceTranslation) => SentenceTranslationReducer(null, {
                    ...action,
                    sentenceTranslation,
                }),
            );

            newState.sort((a, b) => {
                if (a.sentenceNumber < b.sentenceNumber) {
                    return -1;
                }

                if (a.sentenceNumber > b.sentenceNumber) {
                    return 1;
                }

                if (a.paragraphNumber < b.paragraphNumber) {
                    return -1;
                }

                if (a.paragraphNumber > b.paragraphNumber) {
                    return 1;
                }

                return 0;
            });
            return newState;
        }
        case Actions.SetTranslation:
            return state.map((translation) => {
                if (translation.paragraphNumber === action.sentenceTranslation.paragraphNumber &&
                    translation.sentenceNumber === action.sentenceTranslation.sentenceNumber) {
                    return SentenceTranslationReducer(translation, action);
                }
                return translation;
            });
        default:
            return state;
    }
};

export default SentenceTranslationsReducer;
