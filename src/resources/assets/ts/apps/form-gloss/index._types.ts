import { ILexicalEntryInflection } from '@root/connectors/backend/IBookApi';
import { ILexicalEntryEntity } from '@root/connectors/backend/IGlossResourceApi';

export const enum FormSection {
    Gloss = 'gloss',
    Inflections = 'inflections',
}

export interface IProps {
    confirmButton: string;
    lexicalEntry: ILexicalEntryEntity;
    inflections: ILexicalEntryInflection[];
    prefetched: boolean;
    formSections: FormSection[];
}
