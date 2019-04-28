import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    ICardRequest,
    ICardResponse,
} from './FlashcardApiConnector._types';

export default class GlossResourceApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public card(args: ICardRequest) {
        return this._api.value.post<ICardResponse>('/flashcard/card', args);
    }
}
