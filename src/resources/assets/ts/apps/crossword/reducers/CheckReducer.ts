import { Actions, type ICrosswordAction } from '../actions';

/**
 * Per-cell check results. Key is "{row}_{col}", value is true (correct) or false (incorrect).
 * Absent key means the cell has not been checked yet.
 * Cleared on each new letter entry or when a new check is initiated.
 */
export type CheckReducerState = Record<string, boolean>;

const InitialState: CheckReducerState = {};

const CheckReducer = (state = InitialState, action: ICrosswordAction): CheckReducerState => {
    switch (action.type) {
        case Actions.InitializePuzzle:
        case Actions.LoadDraft:
            return {};

        // Clear check state on any cell edit so stale highlights don't linger.
        case Actions.EnterLetter:
        case Actions.DeleteLetter: {
            if (!action.cellKey || !(action.cellKey in state)) return state;
            const next = { ...state };
            delete next[action.cellKey];
            return next;
        }

        case Actions.RevealCells: {
            if (!action.revealCells) return state;
            const next = { ...state };
            for (const key of Object.keys(action.revealCells)) {
                delete next[key];
            }
            return next;
        }

        case Actions.ClearCheck:
            return {};

        case Actions.CheckResult:
            return { ...action.checkResults };

        default:
            return state;
    }
};

export default CheckReducer;
