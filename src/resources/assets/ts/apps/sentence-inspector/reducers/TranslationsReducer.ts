import { Actions } from '../actions';
import { ParagraphState } from './FragmentsReducer._types';
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
                paragraphs: action.sentence.sentenceTranslations //
                    .reduce((carry, item) => {
                        if (carry.previousParagraph < item.paragraphNumber) {
                            carry.previousParagraph = item.paragraphNumber;
                            carry.paragraphs.push([]);
                        }
                        const paragraph = carry.paragraphs[carry.paragraphs.length - 1];
                        // Multiple sentences can be in the same paragraph sometimes. In this case, we would want whitespace
                        // between the translation fragments.
                        if (paragraph.length > 0 && paragraph[paragraph.length - 1].sentenceNumber < item.sentenceNumber) {
                            paragraph.push({
                                id: 0,
                                fragment: ' ',
                                sentenceNumber: item.sentenceNumber,
                            });
                        }

                        paragraph.push({
                            id: 0,
                            fragment: item.translation,
                            sentenceNumber: item.sentenceNumber,
                        });
                        return carry;
                    }, {
                        paragraphs: [] as ParagraphState[],
                        previousParagraph: -1,
                    }).paragraphs,
            } as TranslationsState;
        default:
            return state;
    }
};

export default TranslationFragmentsReducer;
