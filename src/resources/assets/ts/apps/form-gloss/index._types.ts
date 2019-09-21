import { IGlossEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';

export interface IProps {
    confirmButton: string;
    gloss: IGlossEntity;
    prefetched: boolean;
}
