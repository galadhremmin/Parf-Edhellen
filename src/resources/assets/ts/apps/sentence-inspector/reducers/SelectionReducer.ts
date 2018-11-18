import Actions from './Actions';
import {
    ISelectionReducerAction,
    ISelectionReducerState,
} from './SelectionReducer._types';

const SelectionReducer = (state: ISelectionReducerState = {
    fragmentId: 0,
    sentenceNumber: 0,
}, action: ISelectionReducerAction) => {
    switch (action.type) {
        case Actions.SelectFragment:
            return {
                ...state,
                fragmentId: action.fragmentId,
                sentenceNumber: action.sentenceNumber,
            };
        default:
            return state;
    }
};

export default SelectionReducer;
