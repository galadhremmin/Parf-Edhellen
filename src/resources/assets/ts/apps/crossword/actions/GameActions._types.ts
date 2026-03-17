import type { IReduxAction } from '@root/_types';
import type { ICrosswordClue, ICrosswordPuzzleResponse } from '@root/connectors/backend/ICrosswordApi';

export interface ICrosswordAction extends IReduxAction {
    // InitializePuzzle
    puzzle?: ICrosswordPuzzleResponse;
    // InitializePuzzle / ResumeTimer
    priorSeconds?: number;
    // LoadDraft
    draft?: Record<string, string>;
    // SelectCell
    row?: number | null;
    col?: number | null;
    direction?: 'across' | 'down';
    activeClue?: ICrosswordClue | null;
    // EnterLetter / DeleteLetter
    cellKey?: string;
    letter?: string;
    // CheckResult
    checkResults?: Record<string, boolean>;
    // RevealCells
    revealCells?: Record<string, string>;
    // SetStage
    stage?: CrosswordStage;
    // SetTime
    time?: number;
    // CompletionResult
    daysCompleted?: number;
    secondsElapsed?: number | null;
    isAssisted?: boolean;
}

export const enum CrosswordStage {
    Loading  = 0,
    Playing  = 1,
    Checking = 2,
    Complete = 3,
    GameOver = 4,
}
