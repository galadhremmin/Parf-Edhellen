import Actions from './Actions';
import {
    FragmentsReducerState,
    IFragmentsReducerAction,
} from './FragmentsReducer._types';

const FragmentsReducer = (state: FragmentsReducerState = [], action: IFragmentsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return {
                ...state,
            };
        default:
            return state;
    }
};

export default FragmentsReducer;
