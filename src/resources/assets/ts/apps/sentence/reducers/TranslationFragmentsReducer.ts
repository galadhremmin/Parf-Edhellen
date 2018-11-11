import Actions from './Actions';
import { IFragmentsReducerAction } from './FragmentsReducer._types';
import { TranslationFragmentsState } from './TranslationFragmentsReducer._types';

const TranslationFragmentsReducer = (state: TranslationFragmentsState = [], action: IFragmentsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return state;
        default:
            return state;
    }
};

export default TranslationFragmentsReducer;
