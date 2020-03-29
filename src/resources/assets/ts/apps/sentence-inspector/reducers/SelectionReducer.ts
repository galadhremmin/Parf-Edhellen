import { Actions } from '../actions';
import {
    ISelectionReducerAction,
    ISelectionReducerState,
} from './SelectionReducer._types';

const SelectionReducer = (state: ISelectionReducerState = {
    fragmentId: null,
    sentenceNumber: null,
}, action: ISelectionReducerAction) => {
    switch (action.type) {
        case Actions.SelectFragment:
            return {
                ...state,
                fragmentId: action.fragmentId || null,
                sentenceNumber: action.sentenceNumber || null,
            };
        default:
            return state;
    }
};

export default SelectionReducer;
