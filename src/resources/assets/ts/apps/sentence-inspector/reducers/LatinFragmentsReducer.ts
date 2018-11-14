import convert from '../utilities/LocalizedFragmentConverter';
import Actions from './Actions';
import { LocalizedFragmentsReducerState } from './FragmentsReducer._types';
import { ISentenceReducerAction } from './SentenceReducer._types';

const TransformerName = 'latin';
const LatinFragmentsReducer = (state: LocalizedFragmentsReducerState = [], action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return convert(action.sentence.sentenceTransformations[TransformerName], action.sentence.sentenceFragments);
        default:
            return state;
    }
};

export default LatinFragmentsReducer;
