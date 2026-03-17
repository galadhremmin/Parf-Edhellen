export interface ICrosswordClue {
    number: number;
    direction: 'across' | 'down';
    clue: string;
    row: number;
    col: number;
    length: number;
}

// Field names are camelCase because ApiConnector applies snakeCasePropsToCamelCase to all responses.
export interface ICrosswordPuzzleResponse {
    puzzleId: number;
    date: string;
    languageId: number;
    grid: (string | null)[][];
    clues: ICrosswordClue[];
    /** null for guests */
    completed: boolean | null;
    /** null for guests or if not yet completed */
    daysCompleted: number | null;
    /** null for guests or if not yet completed */
    secondsElapsed: number | null;
}

export interface ICrosswordCompletionResponse {
    daysCompleted: number;
    secondsElapsed: number | null;
    isAssisted: boolean;
}

export interface ICrosswordCheckResponse {
    results: Record<string, boolean>;
    /** Non-null when all answers are correct and user is authenticated. */
    completion: ICrosswordCompletionResponse | null;
}

export interface ICrosswordRevealResponse {
    answer: string;
    cells: Record<string, string>;
}

export interface ICrosswordFillResponse {
    cells: Record<string, string>;
}

export interface ICrosswordApi {
    getPuzzle(languageId: number, date: string): Promise<ICrosswordPuzzleResponse>;
    checkAnswers(
        puzzleId: number,
        cells: Record<string, string>,
        secondsElapsed: number | null,
        isAssisted: boolean,
    ): Promise<ICrosswordCheckResponse>;
    revealClue(puzzleId: number, clueNumber: number, direction: string): Promise<ICrosswordRevealResponse>;
    adminFill(puzzleId: number): Promise<ICrosswordFillResponse>;
}
