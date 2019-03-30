import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/DiscussApiConnector._types';

import { IThreadMetadataState } from '../reducers/ThreadMetadataReducer._types';

export interface IProps {
    post: IPostEntity;
    thread: IThreadEntity;
    threadMetadata: IThreadMetadataState;
}
