import SharedReference from '../../utilities/SharedReference';
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
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public async find(args: IFindRequest) {
        return await this._api.value.post<FindResponse>('book/find', args);
    }

    public async gloss(id: number) {
        return await this._api.value.get<IGlossaryResponse>(`book/translate/${id}`);
    }

    public async glossary(args: IGlossaryRequest) {
        // language_id is an optional parameter and should not be passed as
        // an argument if it is not set.
        if ([0, null].indexOf(args.languageId) > -1) {
            delete args.languageId;
        }

        return await this._api.value.post<IGlossaryResponse>('book/translate', args);
    }

    public async languages() {
        return await this._api.value.get<ILanguagesResponse>('book/languages');
    }

    public async sentence(args: ISentenceRequest) {
        return await this._api.value.get<ISentenceResponse>(`sentence/${args.id}`);
    }
}
