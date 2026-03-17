import type { ReduxThunkDispatch } from '@root/_types';
import type { ICrosswordClue } from '@root/connectors/backend/ICrosswordApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type IRoleManager from '@root/security/IRoleManager';
import type { ICrosswordInitialState } from '../index._types';
import Actions from './Actions';
import { CrosswordStage, type ICrosswordAction } from './GameActions._types';

export default class GameActions {
    constructor(
        private _api = resolve(DI.CrosswordApi),
        private _roles: IRoleManager = resolve(DI.RoleManager),
    ) {}

    // ─── Load ─────────────────────────────────────────────────────────────────

    public loadPuzzle(initialState: ICrosswordInitialState) {
        return async (dispatch: ReduxThunkDispatch) => {
            const puzzleId = initialState.puzzleId;

            // For completed puzzles, do not seed prior seconds — the server's
            // secondsElapsed is authoritative.
            const priorSeconds = initialState.completed
                ? 0
                : this.loadElapsedSeconds(puzzleId);

            dispatch({
                type: Actions.InitializePuzzle,
                // PuzzleReducer reads puzzle as ICrosswordPuzzleResponse-shaped object.
                puzzle: initialState as any,
                priorSeconds,
            } as ICrosswordAction);

            if (initialState.completed === true) {
                // Pre-fill the grid with the server-verified correct answers.
                if (initialState.cells) {
                    dispatch({ type: Actions.LoadDraft, draft: initialState.cells } as ICrosswordAction);
                }
                dispatch({
                    type: Actions.CompletionResult,
                    daysCompleted: initialState.daysCompleted ?? undefined,
                    secondsElapsed: initialState.secondsElapsed ?? undefined,
                    isAssisted: initialState.isAssisted,
                } as ICrosswordAction);
                return;
            }

            // Restore game-over state (persisted so the player can't refresh to reset it).
            if (!this._roles.isAnonymous && this.loadFailed(puzzleId)) {
                dispatch({ type: Actions.SetStage, stage: CrosswordStage.GameOver } as ICrosswordAction);
                return;
            }

            // Restore draft from localStorage if available.
            const draft = this.loadDraft(puzzleId);
            if (draft !== null) {
                dispatch({ type: Actions.LoadDraft, draft } as ICrosswordAction);

                // Auto-check if the draft fills every white cell — the user may have solved
                // this puzzle in a previous session without formally submitting.
                const totalCells = initialState.grid.flat().filter(c => c !== null).length;
                if (Object.keys(draft).length >= totalCells) {
                    await dispatch(this.checkAnswers() as any);
                }
            }
        };
    }

    // ─── Selection ────────────────────────────────────────────────────────────

    public selectCell(row: number, col: number) {
        return (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const clues: ICrosswordClue[] = state.puzzle?.clues ?? [];
            const currentRow: number | null = state.selection?.row ?? null;
            const currentCol: number | null = state.selection?.col ?? null;
            const currentDirection: 'across' | 'down' = state.selection?.direction ?? 'across';

            const isSameCell = row === currentRow && col === currentCol;
            // Toggle direction when re-clicking the same cell.
            const newDirection = isSameCell
                ? (currentDirection === 'across' ? 'down' : 'across')
                : currentDirection;

            const activeClue = this.findClueAtCell(clues, row, col, newDirection)
                ?? this.findClueAtCell(clues, row, col, newDirection === 'across' ? 'down' : 'across');

            dispatch({
                type: Actions.SelectCell,
                row,
                col,
                direction: activeClue?.direction ?? newDirection,
                activeClue: activeClue ?? null,
            } as ICrosswordAction);
        };
    }

    public selectClue(clue: ICrosswordClue) {
        return (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const cells: Record<string, string> = getState().cells ?? {};
            // Jump to first empty cell in the word; fall back to the start cell.
            const startCell = this.firstEmptyCell(clue, cells) ?? { row: clue.row, col: clue.col };
            dispatch({
                type: Actions.SelectCell,
                row: startCell.row,
                col: startCell.col,
                direction: clue.direction,
                activeClue: clue,
            } as ICrosswordAction);
        };
    }

    // ─── Input ────────────────────────────────────────────────────────────────

    public enterLetter(letter: string) {
        return (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const { row, col, activeClue } = state.selection ?? {};
            if (row == null || col == null) return;

            const cellKey = `${row}:${col}`;
            dispatch({ type: Actions.EnterLetter, cellKey, letter } as ICrosswordAction);

            // Save draft after state updates.
            const newCells = { ...state.cells, [cellKey]: letter };
            this.saveDraft(state.puzzle?.puzzleId, newCells);

            // Auto-advance cursor to the next cell in the active word.
            if (activeClue) {
                const next = this.nextCell(activeClue, row, col);
                if (next) {
                    dispatch({
                        type: Actions.SelectCell,
                        row: next.row,
                        col: next.col,
                        direction: activeClue.direction,
                        activeClue,
                    } as ICrosswordAction);
                }
            }
        };
    }

    public deleteLetter() {
        return (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const { row, col, activeClue } = state.selection ?? {};
            if (row == null || col == null) return;

            const cellKey = `${row}:${col}`;
            const currentLetter = state.cells?.[cellKey] ?? '';

            if (currentLetter !== '') {
                // If the current cell has a letter, just clear it.
                dispatch({ type: Actions.DeleteLetter, cellKey } as ICrosswordAction);
                this.saveDraft(state.puzzle?.puzzleId, { ...state.cells, [cellKey]: undefined });
            } else if (activeClue) {
                // If already empty, move back one cell and clear it.
                const prev = this.prevCell(activeClue, row, col);
                if (prev) {
                    const prevKey = `${prev.row}:${prev.col}`;
                    dispatch({ type: Actions.DeleteLetter, cellKey: prevKey } as ICrosswordAction);
                    dispatch({
                        type: Actions.SelectCell,
                        row: prev.row,
                        col: prev.col,
                        direction: activeClue.direction,
                        activeClue,
                    } as ICrosswordAction);
                    this.saveDraft(state.puzzle?.puzzleId, { ...state.cells, [prevKey]: undefined });
                }
            }
        };
    }

    public moveSelection(arrowDirection: 'ArrowUp' | 'ArrowDown' | 'ArrowLeft' | 'ArrowRight') {
        return (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const { row, col, direction: currentWordDir } = state.selection ?? {};
            if (row == null || col == null) return;

            const grid: (string | null)[][] = state.puzzle?.grid ?? [];
            const rows = grid.length;
            const cols = grid[0]?.length ?? 0;

            let newRow = row;
            let newCol = col;
            let newDir = currentWordDir ?? 'across';

            switch (arrowDirection) {
                case 'ArrowLeft':  newCol = Math.max(0, col - 1); newDir = 'across'; break;
                case 'ArrowRight': newCol = Math.min(cols - 1, col + 1); newDir = 'across'; break;
                case 'ArrowUp':    newRow = Math.max(0, row - 1); newDir = 'down'; break;
                case 'ArrowDown':  newRow = Math.min(rows - 1, row + 1); newDir = 'down'; break;
            }

            // Skip black cells.
            if (grid[newRow]?.[newCol] === null) return;

            const clues: ICrosswordClue[] = state.puzzle?.clues ?? [];
            const activeClue = this.findClueAtCell(clues, newRow, newCol, newDir)
                ?? this.findClueAtCell(clues, newRow, newCol, newDir === 'across' ? 'down' : 'across');

            dispatch({
                type: Actions.SelectCell,
                row: newRow,
                col: newCol,
                direction: activeClue?.direction ?? newDir,
                activeClue: activeClue ?? null,
            } as ICrosswordAction);
        };
    }

    public advanceToNextWord() {
        return (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const clues: ICrosswordClue[] = state.puzzle?.clues ?? [];
            const cells: Record<string, string> = state.cells ?? {};
            const { activeClue } = state.selection ?? {};

            if (clues.length === 0) return;

            const currentIdx = activeClue
                ? clues.findIndex(c => c.number === activeClue.number && c.direction === activeClue.direction)
                : -1;

            // Cycle through clues looking for the next one with empty cells.
            for (let i = 1; i <= clues.length; i++) {
                const next = clues[(currentIdx + i) % clues.length];
                const empty = this.firstEmptyCell(next, cells);
                if (empty) {
                    dispatch({
                        type: Actions.SelectCell,
                        row: empty.row,
                        col: empty.col,
                        direction: next.direction,
                        activeClue: next,
                    } as ICrosswordAction);
                    return;
                }
            }
        };
    }

    // ─── Check & reveal ───────────────────────────────────────────────────────

    public checkAnswers() {
        return async (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const puzzleId: number = state.puzzle?.puzzleId;
            const cells: Record<string, string> = state.cells ?? {};
            const checksRemaining: number = state.stage?.checksRemaining ?? 0;
            const isFinal = checksRemaining === 0;
            if (!puzzleId) return;

            // Peek mode: toggle — clicking Check again hides current results.
            if (!isFinal && Object.keys(state.check ?? {}).length > 0) {
                dispatch({ type: Actions.ClearCheck } as ICrosswordAction);
                return;
            }

            dispatch({ type: Actions.SetStage, stage: CrosswordStage.Checking } as ICrosswordAction);

            // Strip whitespace-only cells (e.g. spaces from admin-fill answers) so they
            // are not sent as null after Laravel's TrimStrings/ConvertEmptyStrings middleware.
            const filteredCells: Record<string, string> = {};
            for (const [k, v] of Object.entries(cells)) {
                if (v.trim() !== '') filteredCells[k] = v;
            }

            // Send elapsed time and assist flag so the server can record a completion.
            const secondsElapsed = state.stage?.startTime
                ? Math.max(0, Math.round((state.stage.time - state.stage.startTime) / 1000))
                : null;
            const isAssisted: boolean = state.stage?.isAssisted ?? false;

            const result = await this._api.checkAnswers(puzzleId, filteredCells, secondsElapsed, isAssisted);
            dispatch({ type: Actions.CheckResult, checkResults: result.results } as ICrosswordAction);

            if (result.completion !== null) {
                // Server confirmed all correct and recorded a completion.
                this.clearElapsedSeconds(puzzleId);
                this.clearDraft(puzzleId);
                dispatch({
                    type: Actions.CompletionResult,
                    daysCompleted: result.completion.daysCompleted,
                    secondsElapsed: result.completion.secondsElapsed ?? undefined,
                    isAssisted: result.completion.isAssisted,
                } as ICrosswordAction);
                return;
            }

            const allCorrect = Object.values(result.results).length > 0
                && Object.values(result.results).every(Boolean);

            // Check if every white cell has been filled and is correct.
            const grid: (string | null)[][] = state.puzzle?.grid ?? [];
            const totalCells = grid.flat().filter(c => c !== null).length;
            const allFilled = Object.keys(cells).length >= totalCells;

            if (isFinal && !(allCorrect && allFilled)) {
                // Final submit with wrong/missing answers → game over.
                this.saveFailed(puzzleId);
                dispatch({ type: Actions.SetStage, stage: CrosswordStage.GameOver } as ICrosswordAction);
            } else {
                // Peek check: show highlights, decrement counter, keep playing.
                dispatch({ type: Actions.UseCheck } as ICrosswordAction);
                dispatch({ type: Actions.SetStage, stage: CrosswordStage.Playing } as ICrosswordAction);
            }
        };
    }

    public revealClue(clueNumber: number, direction: string) {
        return async (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const puzzleId: number = state.puzzle?.puzzleId;
            const revealsRemaining: number = state.stage?.revealsRemaining ?? 0;
            if (!puzzleId || revealsRemaining === 0) return;

            const result = await this._api.revealClue(puzzleId, clueNumber, direction);
            dispatch({ type: Actions.RevealCells, revealCells: result.cells } as ICrosswordAction);
            dispatch({ type: Actions.UseReveal } as ICrosswordAction);
            dispatch({ type: Actions.SetIsAssisted, isAssisted: true } as ICrosswordAction);

            const newCells = { ...state.cells, ...result.cells };
            this.saveDraft(puzzleId, newCells);
        };
    }

    // ─── Timer ────────────────────────────────────────────────────────────────

    public pauseTimer() {
        return (_dispatch: ReduxThunkDispatch, getState: () => any) => {
            const state = getState();
            const { startTime, time } = state.stage ?? {};
            const puzzleId: number | undefined = state.puzzle?.puzzleId;
            if (!puzzleId || !startTime) return;

            const elapsed = Math.max(0, Math.round((time - startTime) / 1000));
            this.saveElapsedSeconds(puzzleId, elapsed);
        };
    }

    public resumeTimer() {
        return (dispatch: ReduxThunkDispatch, getState: () => any) => {
            const puzzleId: number | undefined = getState().puzzle?.puzzleId;
            const prior = this.loadElapsedSeconds(puzzleId);
            dispatch({ type: Actions.ResumeTimer, priorSeconds: prior } as ICrosswordAction);
        };
    }

    public tickTimer(time: number) {
        return { type: Actions.SetTime, time } as ICrosswordAction;
    }

    // ─── Admin ────────────────────────────────────────────────────────────────

    public adminFill() {
        return async (dispatch: ReduxThunkDispatch, getState: () => any) => {
            if (!this._roles.isAdministrator) return;
            const state = getState();
            const puzzleId: number = state.puzzle?.puzzleId;
            if (!puzzleId) return;

            const result = await this._api.adminFill(puzzleId);
            // Use RevealCells so check state is cleared and draft is updated.
            dispatch({ type: Actions.RevealCells, revealCells: result.cells } as ICrosswordAction);
            const newCells = { ...state.cells, ...result.cells };
            this.saveDraft(puzzleId, newCells);
        };
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private findClueAtCell(clues: ICrosswordClue[], row: number, col: number, direction: 'across' | 'down'): ICrosswordClue | null {
        return clues.find(c => {
            if (c.direction !== direction) return false;
            if (direction === 'across') {
                return c.row === row && col >= c.col && col < c.col + c.length;
            }
            return c.col === col && row >= c.row && row < c.row + c.length;
        }) ?? null;
    }

    private firstEmptyCell(clue: ICrosswordClue, cells: Record<string, string>): { row: number; col: number } | null {
        const dr = clue.direction === 'across' ? 0 : 1;
        const dc = clue.direction === 'across' ? 1 : 0;
        for (let i = 0; i < clue.length; i++) {
            const r = clue.row + i * dr;
            const c = clue.col + i * dc;
            if (!cells[`${r}:${c}`]) {
                return { row: r, col: c };
            }
        }
        return null;
    }

    private nextCell(clue: ICrosswordClue, row: number, col: number): { row: number; col: number } | null {
        const dr = clue.direction === 'across' ? 0 : 1;
        const dc = clue.direction === 'across' ? 1 : 0;
        const endRow = clue.row + (clue.length - 1) * dr;
        const endCol = clue.col + (clue.length - 1) * dc;
        if (row === endRow && col === endCol) return null;
        return { row: row + dr, col: col + dc };
    }

    private prevCell(clue: ICrosswordClue, row: number, col: number): { row: number; col: number } | null {
        if (row === clue.row && col === clue.col) return null;
        const dr = clue.direction === 'across' ? 0 : 1;
        const dc = clue.direction === 'across' ? 1 : 0;
        return { row: row - dr, col: col - dc };
    }

    private saveDraft(puzzleId: number | undefined, cells: Record<string, string | undefined>): void {
        if (!puzzleId) return;
        try {
            // Remove undefined entries.
            const clean: Record<string, string> = {};
            for (const [k, v] of Object.entries(cells)) {
                if (v !== undefined) clean[k] = v;
            }
            window.localStorage.setItem(`ed.crossword.${puzzleId}`, JSON.stringify(clean));
        } catch {
            // localStorage unavailable (SSR, private mode)
        }
    }

    private loadDraft(puzzleId: number | undefined): Record<string, string> | null {
        if (!puzzleId) return null;
        try {
            const raw = window.localStorage.getItem(`ed.crossword.${puzzleId}`);
            return raw ? JSON.parse(raw) as Record<string, string> : null;
        } catch {
            return null;
        }
    }

    private clearDraft(puzzleId: number | undefined): void {
        if (!puzzleId) return;
        try {
            window.localStorage.removeItem(`ed.crossword.${puzzleId}`);
        } catch { }
    }

    private saveFailed(puzzleId: number): void {
        try {
            window.localStorage.setItem(`ed.crossword.${puzzleId}.failed`, '1');
        } catch {
            // localStorage unavailable
        }
    }

    private loadFailed(puzzleId: number): boolean {
        try {
            return window.localStorage.getItem(`ed.crossword.${puzzleId}.failed`) === '1';
        } catch {
            return false;
        }
    }

    private saveElapsedSeconds(puzzleId: number | undefined, seconds: number): void {
        if (!puzzleId) return;
        try {
            window.localStorage.setItem(`ed.crossword.${puzzleId}.seconds`, String(seconds));
        } catch { }
    }

    private loadElapsedSeconds(puzzleId: number | undefined): number {
        if (!puzzleId) return 0;
        try {
            const raw = window.localStorage.getItem(`ed.crossword.${puzzleId}.seconds`);
            return raw ? Math.max(0, parseInt(raw, 10)) : 0;
        } catch {
            return 0;
        }
    }

    private clearElapsedSeconds(puzzleId: number | undefined): void {
        if (!puzzleId) return;
        try {
            window.localStorage.removeItem(`ed.crossword.${puzzleId}.seconds`);
        } catch { }
    }
}
