import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import IFlashcardApi, {
    ICardRequest,
    ICardResponse,
    ICardTestRequest,
    ICardTestResponse,
} from './IFlashcardApi';

export default class FlashcardApiConnector implements IFlashcardApi {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public card(args: ICardRequest) {
        return this._api.value.post<ICardResponse>('/flashcard/card', args);
    }

    public test(args: ICardTestRequest) {
        return this._api.value.post<ICardTestResponse>('/flashcard/test', args);
    }
}
