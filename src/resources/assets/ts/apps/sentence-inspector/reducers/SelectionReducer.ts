import { Actions } from '../actions';
import {
    ISelectionReducerAction,
    ISelectionReducerState,
} from './SelectionReducer._types';

const SelectionReducer = (state: ISelectionReducerState = null, action: ISelectionReducerAction) => {
    switch (action.type) {
        case Actions.SelectFragment:
            return action.fragment;
        default:
            return state;
    }
};

export default SelectionReducer;
