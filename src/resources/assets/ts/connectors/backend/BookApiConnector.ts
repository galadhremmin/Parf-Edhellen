import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type IBookApi from './IBookApi';
import type {
    IEntitiesRequest,
    IEntitiesResponse,
    IFindRequest,
    IFindResponse,
    IGlossaryResponse,
    ILanguagesResponse,
    ISentenceRequest,
    ISentenceResponse,
    ISpecificEntityRequest,
} from './IBookApi';
import type { ILexicalEntryGroup } from './IGlossResourceApi';

export default class BookApiConnector implements IBookApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public entities<T>({ groupId, data }: IEntitiesRequest): Promise<IEntitiesResponse<T>> {
        return this._api.post<IEntitiesResponse<T>>(`book/entities/${groupId}`, data);
    }

    public entity<T = IGlossaryResponse>({ groupId, entityId }: ISpecificEntityRequest<T>): Promise<IEntitiesResponse<T>> {
        return this._api.post<IEntitiesResponse<T>>(`book/entities/${groupId}/${entityId}`, {});
    }

    public find(args: IFindRequest) {
        return this._api.post<IFindResponse>('book/find', args);
    }

    public lexicalEntry(id: number) {
        return this._api.get<IGlossaryResponse>(`book/translate/${id}`);
    }

    public lexicalEntryFromVersion(id: number): Promise<IGlossaryResponse> {
        return this._api.get<IGlossaryResponse>(`book/translate/version/${id}`);
    }

    public groups() {
        return this._api.get<ILexicalEntryGroup[]>('book/group');
    }

    public languages() {
        return this._api.get<ILanguagesResponse>('book/languages');
    }

    public sentence(args: ISentenceRequest) {
        return this._api.get<ISentenceResponse>(`sentence/${args.id}`);
    }
}
