import { Actions } from '../actions';
import type {
    DisplayMode,
    IDisplayReducerAction,
    IDisplayReducerState,
} from './DisplayReducer._types';

const defaultState: IDisplayReducerState = {
    changedForms: false,
    latin: true,
    tengwar: true,
    translation: true,
};

/**
 * The two ways of writing the phrase itself. At least one must stay on: with both off the
 * page has a translation and a word list but no text, which is not a phrase page at all.
 */
const Scripts: DisplayMode[] = [ 'tengwar', 'latin' ];

export const isLastScript = (state: IDisplayReducerState, mode: DisplayMode) =>
    Scripts.includes(mode) && Scripts.filter((script) => state[script]).length < 2;

const DisplayReducer = (state: IDisplayReducerState = defaultState, action: IDisplayReducerAction) => {
    switch (action.type) {
        case Actions.ToggleDisplay: {
            const mode = action.mode;
            if (! mode) {
                return state;
            }
            // Turning the last remaining script off would leave nothing to read.
            if (state[mode] && isLastScript(state, mode)) {
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
