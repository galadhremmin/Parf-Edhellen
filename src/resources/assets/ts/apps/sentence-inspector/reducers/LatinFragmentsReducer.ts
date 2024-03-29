import { Actions } from '../actions';
import convertTransformationToTextComponents from '../utilities/TextConverter';
import { ITextState } from './FragmentsReducer._types';
import { ISentenceReducerAction } from './SentenceReducer._types';

const TransformerName = 'latin';
const LatinFragmentsReducer = (state: ITextState = {
    paragraphs: [],
    transformerName: TransformerName,
}, action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return convertTransformationToTextComponents(TransformerName, action.sentence.sentenceTransformations[TransformerName], //
                action.sentence.sentenceFragments);
        default:
            return state;
    }
};

export default LatinFragmentsReducer;
