import ApiConnector from '../ApiConnector';
import {
    FindResponse,
    IFindRequest,
    IGlossaryRequest,
    IGlossaryResponse,
    ILanguagesResponse,
    ISentenceRequest,
    ISentenceResponse,
} from './BookApiConnector._types';

export default class BookApiConnector {
    constructor(private _api = new ApiConnector()) {
    }

    public async find(args: IFindRequest) {
        return await this._api.post<FindResponse>('book/find', args);
    }

    public async languages() {
        return await this._api.get<ILanguagesResponse>('book/languages');
    }

    public async glossary(args: IGlossaryRequest) {
        // language_id is an optional parameter and should not be passed as
        // an argument if it is not set.
        if ([0, null].indexOf(args.languageId) > -1) {
            delete args.languageId;
        }

        return await this._api.post<IGlossaryResponse>('book/translate', args);
    }

    public async sentence(args: ISentenceRequest) {
        return await this._api.get<ISentenceResponse>(`sentence/${args.id}`);
    }
}
