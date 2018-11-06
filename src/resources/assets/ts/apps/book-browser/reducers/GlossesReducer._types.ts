import { IGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IGlossesState {
    [ languageId: number ]: IGlossEntity[];
}
