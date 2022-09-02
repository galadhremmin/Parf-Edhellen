import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';

export interface IProps {
    confirmButton: string;
    gloss: IGlossEntity;
    inflections: IGlossInflection[];
    prefetched: boolean;
}
