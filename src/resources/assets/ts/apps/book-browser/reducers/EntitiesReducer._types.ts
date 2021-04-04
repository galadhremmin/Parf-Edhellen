import { IGlossaryResponse } from '@root/connectors/backend/IBookApi';
import { IReduxAction } from '@root/_types';

export interface IEntitiesAction<T = IGlossaryResponse> extends IReduxAction {
    entities: T;
    groupId: number;
    groupIntlName: string;
    single: boolean;
    word: string;
}

export interface IEntitiesState {
    groupId: number;
    groupIntlName: string;
    loading: boolean;
    single: boolean;
    word: string;
}
