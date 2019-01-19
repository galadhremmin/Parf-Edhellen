import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    IPostEntity,
} from './ForumApiConnector._types';

export default class ForumApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public card(args: ICardRequest) {
        return this._api.value.post<IPostEntity[]>('/flashcard/card', args);
    }

    public test(args: ICardTestRequest) {
        return this._api.value.post<ICardTestResponse>('/flashcard/test', args);
    }
}
