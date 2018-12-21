import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    ICardRequest,
    ICardResponse,
    ICardTestRequest,
    ICardTestResponse,
} from './FlashcardApiConnector._types';

export default class FlashcardApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public card(args: ICardRequest) {
        return this._api.value.post<ICardResponse>('/flashcard/card', args);
    }

    public test(args: ICardTestRequest) {
        return this._api.value.post<ICardTestResponse>('/flashcard/test', args);
    }
}
