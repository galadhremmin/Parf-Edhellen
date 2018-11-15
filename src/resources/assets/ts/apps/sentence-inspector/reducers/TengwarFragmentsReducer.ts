import convert from '../utilities/TextConverter';
import Actions from './Actions';
import { ITextState } from './FragmentsReducer._types';
import { ISentenceReducerAction } from './SentenceReducer._types';

const TransformerName = 'tengwar';
const TengwarFragmentsReducer = (state: ITextState = {
    paragraphs: [],
    transformerName: TransformerName,
}, action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return convert(TransformerName, action.sentence.sentenceTransformations[TransformerName], action.sentence.sentenceFragments);
        default:
            return state;
    }
};

export default TengwarFragmentsReducer;
