import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import IBookApi, {
    IFindResponse,
    IEntitiesRequest,
    IEntitiesResponse,
    IFindRequest,
    IGlossaryResponse,
    ILanguagesResponse,
    ISentenceRequest,
    ISentenceResponse,
} from './IBookApi';
import { IGlossGroup } from './IGlossResourceApi';

export default class BookApiConnector implements IBookApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    entities<T>({ groupId, data }: IEntitiesRequest): Promise<IEntitiesResponse<T>> {
        return this._api.post<IEntitiesResponse<T>>(`book/entities/${groupId}`, data);
    }

    public find(args: IFindRequest) {
        return this._api.post<IFindResponse>('book/find', args);
    }

    public gloss(id: number) {
        return this._api.get<IGlossaryResponse>(`book/translate/${id}`);
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
}
