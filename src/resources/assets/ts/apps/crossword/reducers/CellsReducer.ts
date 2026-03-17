import { Actions, type ICrosswordAction } from '../actions';

/** User's typed letters. Key is "{row}_{col}". */
export type CellsReducerState = Record<string, string>;

const InitialState: CellsReducerState = {};

const CellsReducer = (state = InitialState, action: ICrosswordAction): CellsReducerState => {
    switch (action.type) {
        case Actions.InitializePuzzle:
            return {};

        case Actions.LoadDraft:
            return { ...action.draft };

        case Actions.EnterLetter: {
            if (!action.cellKey || !action.letter) return state;
            return { ...state, [action.cellKey]: action.letter };
        }

        case Actions.DeleteLetter: {
            if (!action.cellKey) return state;
            const next = { ...state };
            delete next[action.cellKey];
            return next;
        }

        case Actions.RevealCells:
            return { ...state, ...action.revealCells };

        default:
            return state;
    }
};

export default CellsReducer;
