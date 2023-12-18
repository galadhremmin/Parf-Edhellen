import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import {
    IWordFinderApi,
    IWordFinderGame,
} from './IWordFinderApi';

export default class WordFinderConnector implements IWordFinderApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    newGame(languageId: number): Promise<IWordFinderGame> {
        return this._api.get<IWordFinderGame>(`games/word-finder/${languageId}`);
    }
}
