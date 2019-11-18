import { IReduxAction } from '@root/_types';
import { IGlossaryResponse } from '@root/connectors/backend/IBookApi';

export interface IGlossaryState {
    loading: boolean;
    single: boolean;
    word: string;
}

export interface IGlossaryAction extends IReduxAction {
    glossary: IGlossaryResponse;
}
