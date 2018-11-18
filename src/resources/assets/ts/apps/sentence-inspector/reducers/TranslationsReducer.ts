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
                    .map((paragraphNumber) => {
                        const paragraph = action.sentence.sentenceTranslations[paragraphNumber];

                        return [{
                            fragment: paragraph.translation,
                            sentenceNumber: paragraph.sentenceNumber,
                        }];
                    }),
            };
        default:
            return state;
    }
};

export default TranslationFragmentsReducer;
