import SharedReference from '../../utilities/SharedReference';
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
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public find(args: IFindRequest) {
        return this._api.value.post<FindResponse>('book/find', args);
    }

    public gloss(id: number) {
        return this._api.value.get<IGlossaryResponse>(`book/translate/${id}`);
    }

    public glossary(args: IGlossaryRequest) {
        // language_id is an optional parameter and should not be passed as
        // an argument if it is not set.
        if ([0, null].indexOf(args.languageId) > -1) {
            delete args.languageId;
        }

        return this._api.value.post<IGlossaryResponse>('book/translate', args);
    }

    public groups() {
        return this._api.value.get<IGlossGroup[]>('book/group');
    }

    public languages() {
        return this._api.value.get<ILanguagesResponse>('book/languages');
    }

    public sentence(args: ISentenceRequest) {
        return this._api.value.get<ISentenceResponse>(`sentence/${args.id}`);
    }

    public suggest(args: ISuggestRequest) {
        return this._api.value.post<ISuggestResponse>(`book/suggest`, args);
    }
}
