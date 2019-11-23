import { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';

export interface IProps {
    confirmButton: string;
    gloss: IGlossEntity;
    prefetched: boolean;
}
