import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type {
    ICrosswordApi,
    ICrosswordCheckResponse,
    ICrosswordFillResponse,
    ICrosswordPuzzleResponse,
    ICrosswordRevealResponse,
} from './ICrosswordApi';

export default class CrosswordConnector implements ICrosswordApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    getPuzzle(languageId: number, date: string): Promise<ICrosswordPuzzleResponse> {
        return this._api.get<ICrosswordPuzzleResponse>(`games/crossword/${languageId}/${date}`);
    }

    checkAnswers(
        puzzleId: number,
        cells: Record<string, string>,
        secondsElapsed: number | null,
        isAssisted: boolean,
    ): Promise<ICrosswordCheckResponse> {
        return this._api.post<ICrosswordCheckResponse>('games/crossword/check', {
            puzzle_id:       puzzleId,
            cells,
            seconds_elapsed: secondsElapsed,
            is_assisted:     isAssisted,
        });
    }

    revealClue(puzzleId: number, clueNumber: number, direction: string): Promise<ICrosswordRevealResponse> {
        return this._api.post<ICrosswordRevealResponse>('games/crossword/reveal', {
            puzzle_id:    puzzleId,
            clue_number:  clueNumber,
            direction,
        });
    }

    adminFill(puzzleId: number): Promise<ICrosswordFillResponse> {
        return this._api.get<ICrosswordFillResponse>(`games/crossword/${puzzleId}/admin-fill`);
    }
}
