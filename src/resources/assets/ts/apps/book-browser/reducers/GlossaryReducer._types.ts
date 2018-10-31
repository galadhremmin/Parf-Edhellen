import { IReduxAction } from '../../../_types';
import { IGlossaryResponse } from '../../../connectors/backend/BookApiConnector._types';

export interface IGlossaryState {
    loading: boolean;
    single: boolean;
    word: string;
}

export interface IGlossaryAction extends IReduxAction {
    glossary: IGlossaryResponse;
}
