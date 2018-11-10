import convert from '../utilities/LocalizedFragmentConverter';
import Actions from './Actions';
import {
    IFragmentsReducerAction,
    LocalizedFragmentsReducerState,
} from './FragmentsReducer._types';

const TengwarFragmentsReducer = (state: LocalizedFragmentsReducerState = [], action: IFragmentsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return convert(action.sentence.tengwar, action.sentence.fragments);
        default:
            return state;
    }
};

export default TengwarFragmentsReducer;
