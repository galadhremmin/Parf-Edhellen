import { SearchResultGroups } from '@root/config';
import { IGlossaryResponse } from '@root/connectors/backend/IBookApi';
import { IReduxAction } from '@root/_types';

export interface IEntitiesAction<T = IGlossaryResponse> extends IReduxAction {
    entities: T;
    groupId: keyof typeof SearchResultGroups;
    single: boolean;
    word: string;
}

export interface IEntitiesState {
    groupId: keyof typeof SearchResultGroups;
    loading: boolean;
    single: boolean;
    word: string;
}
