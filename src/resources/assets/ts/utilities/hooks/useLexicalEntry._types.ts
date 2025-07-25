import { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';

export interface IHookedLexicalEntry<T extends ILexicalEntryEntity = ILexicalEntryEntity> {
    error: string | null;
    lexicalEntry: T;
}

export interface IGlossHookOptions<T = any> {
    adapter?: (gloss: ILexicalEntryEntity) => T;
    isEnabled?: boolean;
    isVersion?: boolean;
}
