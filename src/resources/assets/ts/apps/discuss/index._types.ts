import { IThreadResponse } from '@root/connectors/backend/DiscussApiConnector._types';

export interface IProps {
    entityId: number;
    entityType: string;
    discussData: IThreadResponse;
}
