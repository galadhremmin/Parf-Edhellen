import type { ILexicalEntryInflection } from '@root/connectors/backend/IBookApi';
import type { ILexicalEntryEntity } from '@root/connectors/backend/IGlossResourceApi';

export const enum FormSection {
    LexicalEntry = 'lexical_entry',
    Inflections = 'inflections',
}

export interface IProps {
    confirmButton: string;
    lexicalEntry: ILexicalEntryEntity;
    inflections: ILexicalEntryInflection[];
    prefetched: boolean;
    formSections: FormSection[];
}
