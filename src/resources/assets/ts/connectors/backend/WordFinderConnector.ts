import { resolve, DI } from '@root/di';
import ApiConnector from '../ApiConnector';
import {
    IWordFinderApi,
    IWordFinderGame,
} from './IWordFinderApi';

export default class WordFinderConnector implements IWordFinderApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    newGame(languageId: number): Promise<IWordFinderGame> {
        return this._api.get<IWordFinderGame>(`games/word-finder/${languageId}`);
    }
}
