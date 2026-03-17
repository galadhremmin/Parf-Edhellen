import type { ICrosswordClue } from '@root/connectors/backend/ICrosswordApi';
import { Actions, type ICrosswordAction } from '../actions';

export interface ISelectionReducerState {
    row: number | null;
    col: number | null;
    direction: 'across' | 'down';
    activeClue: ICrosswordClue | null;
}

const InitialState: ISelectionReducerState = {
    row: null,
    col: null,
    direction: 'across',
    activeClue: null,
};

const SelectionReducer = (state = InitialState, action: ICrosswordAction): ISelectionReducerState => {
    switch (action.type) {
        case Actions.InitializePuzzle:
            return InitialState;

        case Actions.SelectCell:
            return {
                row: action.row ?? null,
                col: action.col ?? null,
                direction: action.direction ?? state.direction,
                activeClue: action.activeClue ?? null,
            };

        default:
            return state;
    }
};

export default SelectionReducer;
