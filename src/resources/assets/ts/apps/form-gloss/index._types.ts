import { IGlossEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';

export interface IProps {
    gloss: IGlossEntity;
    prefetched: boolean;
}
