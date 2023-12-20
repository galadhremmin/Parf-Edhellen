import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import IFlashcardApi, {
    ICardRequest,
    ICardResponse,
    ICardTestRequest,
    ICardTestResponse,
} from './IFlashcardApi';

export default class FlashcardApiConnector implements IFlashcardApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public card(args: ICardRequest) {
        return this._api.post<ICardResponse>('/flashcard/card', args);
    }

    public test(args: ICardTestRequest) {
        return this._api.post<ICardTestResponse>('/flashcard/test', args);
    }
}
