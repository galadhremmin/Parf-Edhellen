import type { ICrosswordClue } from '@root/connectors/backend/ICrosswordApi';

/**
 * The initial state injected by the server via data-inject-prop-initial-state.
 * Contains the puzzle structure (grid + clues without answers) and, for users
 * who have already completed the puzzle, the pre-filled cell answers.
 *
 * The Injector applies snakeCasePropsToCamelCase automatically, so PHP snake_case
 * keys arrive here as camelCase.
 */
export interface ICrosswordInitialState {
    puzzleId: number;
    date: string;
    languageId: number;
    grid: (string | null)[][];
    clues: ICrosswordClue[];
    /** null = guest (not authenticated) */
    completed: boolean | null;
    daysCompleted: number | null;
    secondsElapsed: number | null;
    isAssisted: boolean;
    /** Pre-filled correct answers — only present for authenticated completed users. */
    cells: Record<string, string> | null;
}

export interface ICrosswordProps {
    languageId: number;
    date: string;
    // isAuthenticated removed — use IRoleManager.isAnonymous via DI
    initialState: ICrosswordInitialState;
}
