import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';

export interface IGlossesState {
    [ languageId: number ]: IBookGlossEntity[];
}
