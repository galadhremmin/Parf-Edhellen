import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import IFlashcardApi, {
    ICardRequest,
    ICardResponse,
    ICardTestRequest,
    ICardTestResponse,
} from './IFlashcardApi';

export default class FlashcardApiConnector implements IFlashcardApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public card(args: ICardRequest) {
        return this._api.post<ICardResponse>('/flashcard/card', args);
    }

    public test(args: ICardTestRequest) {
        return this._api.post<ICardTestResponse>('/flashcard/test', args);
    }
}
