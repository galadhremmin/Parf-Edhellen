import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type {
    IBulkEntriesResponse,
    ICheckMembershipResponse,
    IReorderedEntry,
    IWordList,
    IWordListApi,
    IWordListDetailResponse,
    IWordListIndexResponse,
    IWordListShowResponse,
} from './IWordListApi';

export default class WordListApiConnector implements IWordListApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public getAll(lexicalEntryId?: number): Promise<IWordListIndexResponse> {
        const query = lexicalEntryId ? { lexicalEntryId } : null;
        return this._api.get('word-lists', query);
    }

    public get(wordListId: number): Promise<IWordListDetailResponse> {
        return this._api.get(`word-lists/${wordListId}`);
    }

    public create(name: string, description?: string): Promise<IWordListShowResponse> {
        return this._api.post('word-lists', { name, description });
    }

    public update(wordListId: number, changes: Partial<Pick<IWordList, 'name' | 'description' | 'isPublic'>>): Promise<IWordListShowResponse> {
        return this._api.put(`word-lists/${wordListId}`, changes);
    }

    public destroy(wordListId: number): Promise<void> {
        return this._api.delete(`word-lists/${wordListId}`);
    }

    public addEntry(wordListId: number, lexicalEntryId: number): Promise<void> {
        return this._api.post(`word-lists/${wordListId}/entries`, { lexicalEntryId });
    }

    public removeEntry(wordListId: number, lexicalEntryId: number): Promise<void> {
        return this._api.delete(`word-lists/${wordListId}/entries/${lexicalEntryId}`);
    }

    public removeEntries(wordListId: number, lexicalEntryIds: number[]): Promise<IBulkEntriesResponse> {
        return this._api.post(`word-lists/${wordListId}/entries/bulk-delete`, { lexicalEntryIds });
    }

    public moveEntries(wordListId: number, lexicalEntryIds: number[], targetWordListId: number, copy = false): Promise<IBulkEntriesResponse> {
        return this._api.post(`word-lists/${wordListId}/entries/bulk-move`, {
            lexicalEntryIds,
            targetWordListId,
            copy,
        });
    }

    public reorderEntries(wordListId: number, entries: IReorderedEntry[]): Promise<void> {
        return this._api.put(`word-lists/${wordListId}/entries/reorder`, { entries });
    }

    public checkMembership(lexicalEntryIds: number[]): Promise<ICheckMembershipResponse> {
        return this._api.post('word-lists/check-membership', { lexicalEntryIds });
    }
}
