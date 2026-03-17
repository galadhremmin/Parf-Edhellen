import type { ICrosswordClue, ICrosswordPuzzleResponse } from '@root/connectors/backend/ICrosswordApi';
import { Actions, type ICrosswordAction } from '../actions';

export interface IPuzzleReducerState {
    puzzleId: number | null;
    languageId: number | null;
    date: string | null;
    grid: (string | null)[][] | null;
    clues: ICrosswordClue[];
}

const InitialState: IPuzzleReducerState = {
    puzzleId: null,
    languageId: null,
    date: null,
    grid: null,
    clues: [],
};

const PuzzleReducer = (state = InitialState, action: ICrosswordAction): IPuzzleReducerState => {
    switch (action.type) {
        case Actions.InitializePuzzle: {
            const p = action.puzzle as ICrosswordPuzzleResponse;
            return {
                puzzleId: p.puzzleId,
                languageId: p.languageId,
                date: p.date,
                grid: p.grid,
                clues: p.clues,
            };
        }
        default:
            return state;
    }
};

export default PuzzleReducer;
