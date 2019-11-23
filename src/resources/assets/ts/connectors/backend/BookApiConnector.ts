import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import IBookApi, {
    FindResponse,
    IFindRequest,
    IGlossaryRequest,
    IGlossaryResponse,
    ILanguagesResponse,
    ISentenceRequest,
    ISentenceResponse,
    ISuggestRequest,
    ISuggestResponse,
} from './IBookApi';
import { IGlossGroup } from './IGlossResourceApi';

export default class BookApiConnector implements IBookApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public find(args: IFindRequest) {
        return this._api.post<FindResponse>('book/find', args);
    }

    public gloss(id: number) {
        return this._api.get<IGlossaryResponse>(`book/translate/${id}`);
    }

    public glossary(args: IGlossaryRequest) {
        // language_id is an optional parameter and should not be passed as
        // an argument if it is not set.
        if ([0, null].indexOf(args.languageId) > -1) {
            delete args.languageId;
        }

        return this._api.post<IGlossaryResponse>('book/translate', args);
    }

    public groups() {
        return this._api.get<IGlossGroup[]>('book/group');
    }

    public languages() {
        return this._api.get<ILanguagesResponse>('book/languages');
    }

    public sentence(args: ISentenceRequest) {
        return this._api.get<ISentenceResponse>(`sentence/${args.id}`);
    }

    public suggest(args: ISuggestRequest) {
        return this._api.post<ISuggestResponse>(`book/suggest`, args);
    }
}
