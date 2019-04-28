import { IGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IProps {
    gloss: IGlossEntity;
    prefetched: boolean;
}
