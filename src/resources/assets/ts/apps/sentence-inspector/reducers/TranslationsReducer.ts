import Actions from './Actions';
import { ISentenceReducerAction } from './SentenceReducer._types';
import {
    ITranslationState,
    TranslationsState,
} from './TranslationsReducer._types';

const TranslationFragmentsReducer = (state: TranslationsState = [], action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return Object.keys(action.sentence.sentenceTranslations).map((sentenceNumber) => ({
                fragment: action.sentence.sentenceTranslations[sentenceNumber],
                sentenceNumber: parseInt(sentenceNumber, 10),
            }) as ITranslationState);
        default:
            return state;
    }
};

export default TranslationFragmentsReducer;
