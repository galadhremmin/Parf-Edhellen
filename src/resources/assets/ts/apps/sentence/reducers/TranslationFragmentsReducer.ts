import Actions from './Actions';
import { ISentenceReducerAction } from './SentenceReducer._types';
import { TranslationFragmentsState } from './TranslationFragmentsReducer._types';

const TranslationFragmentsReducer = (state: TranslationFragmentsState = [], action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return state;
        default:
            return state;
    }
};

export default TranslationFragmentsReducer;
