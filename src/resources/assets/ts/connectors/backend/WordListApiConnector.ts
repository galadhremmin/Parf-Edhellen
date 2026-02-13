import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type {
    ICheckMembershipResponse,
    IWordListApi,
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

    public create(name: string, description?: string): Promise<IWordListShowResponse> {
        return this._api.post('word-lists', { name, description });
    }

    public addEntry(wordListId: number, lexicalEntryId: number): Promise<void> {
        return this._api.post(`word-lists/${wordListId}/entries`, { lexicalEntryId });
    }

    public removeEntry(wordListId: number, lexicalEntryId: number): Promise<void> {
        return this._api.delete(`word-lists/${wordListId}/entries/${lexicalEntryId}`);
    }

    public checkMembership(lexicalEntryIds: number[]): Promise<ICheckMembershipResponse> {
        return this._api.post('word-lists/check-membership', { lexicalEntryIds });
    }
}
