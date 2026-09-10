import { Actions } from '../actions';
import type {
    IDisplayReducerAction,
    IDisplayReducerState,
} from './DisplayReducer._types';

const defaultState: IDisplayReducerState = {
    changedForms: false,
    latin: true,
    tengwar: true,
    translation: true,
};

const DisplayReducer = (state: IDisplayReducerState = defaultState, action: IDisplayReducerAction) => {
    switch (action.type) {
        case Actions.ToggleDisplay: {
            const mode = action.mode;
            if (! mode) {
                return state;
            }
            return { ...state, [mode]: ! state[mode] };
        }

        case Actions.ReceiveSentence:
            return defaultState;

        default:
            return state;
    }
};

export default DisplayReducer;
