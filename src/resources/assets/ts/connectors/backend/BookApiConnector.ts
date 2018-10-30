import ApiConnector from '../ApiConnector';
import {
    FindActionResponse,
    IFindActionRequest,
    ILanguagesResponse,
} from './BookApiConnector._types';

export default class BookApiConnector {
    constructor(private _api = new ApiConnector()) {
    }

    public async find(args: IFindActionRequest) {
        const response = await this._api.post<FindActionResponse>('book/find', args);
        return response;
    }

    public async languages() {
        const response = await this._api.get<ILanguagesResponse>('book/languages');
        return response;
    }
}
