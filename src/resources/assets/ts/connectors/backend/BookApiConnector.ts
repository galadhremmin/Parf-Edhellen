import ApiConnector from '../ApiConnector';
import {
    FindResponse,
    IFindRequest,
    IGlossaryRequest,
    IGlossaryResponse,
    ILanguagesResponse,
} from './BookApiConnector._types';

export default class BookApiConnector {
    constructor(private _api = new ApiConnector()) {
    }

    public async find(args: IFindRequest) {
        const response = await this._api.post<FindResponse>('book/find', args);
        return response;
    }

    public async languages() {
        const response = await this._api.get<ILanguagesResponse>('book/languages');
        return response;
    }

    public async glossary(args: IGlossaryRequest) {
        const response = await this._api.post<IGlossaryResponse>('book/translate', args);
        return response;
    }
}
