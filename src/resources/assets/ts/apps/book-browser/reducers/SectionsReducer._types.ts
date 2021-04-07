import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';

export interface ISectionsState<T = IBookGlossEntity> {
    [ languageId: number ]: T[];
}
