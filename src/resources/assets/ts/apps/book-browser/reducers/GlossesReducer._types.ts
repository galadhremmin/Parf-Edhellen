import { IBookGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IGlossesState {
    [ languageId: number ]: IBookGlossEntity[];
}
