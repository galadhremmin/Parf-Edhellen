import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';

export const enum FormSection {
    Gloss = 'gloss',
    Inflections = 'inflections',
}

export interface IProps {
    confirmButton: string;
    gloss: IGlossEntity;
    inflections: IGlossInflection[];
    prefetched: boolean;
    formSections: FormSection[];
}
