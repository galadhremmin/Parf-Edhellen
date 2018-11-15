import Actions from './Actions';
import { ISentenceReducerAction } from './SentenceReducer._types';
import { TranslationsState } from './TranslationsReducer._types';

const TranslationFragmentsReducer = (state: TranslationsState = {
    paragraphs: [],
    transformerName: 'english',
}, action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return {
                ...state,
                paragraphs: Object.keys(action.sentence.sentenceTranslations) //
                    .map((sentenceNumber) => [{
                        fragment: action.sentence.sentenceTranslations[sentenceNumber],
                        sentenceNumber: parseInt(sentenceNumber, 10),
                    }]),
            }
        default:
            return state;
    }
};

export default TranslationFragmentsReducer;
