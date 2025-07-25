import { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';

export interface ISectionsState<T = ILexicalEntryEntity> {
    [ languageId: number ]: T[];
}
