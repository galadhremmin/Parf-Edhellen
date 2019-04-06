import { ComponentEventHandler } from '@root/components/Component._types';
import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/DiscussApiConnector._types';

import { IThreadMetadataState } from '../reducers/ThreadMetadataReducer._types';

export interface IThreadMetadataArgs {
    forumPostId: number[];
    forumThreadId: number;
}

export interface IProps {
    onPostChange?: ComponentEventHandler<number>;
    onThreadMetadataChange?: ComponentEventHandler<IThreadMetadataArgs>;
    post: IPostEntity;
    thread: IThreadEntity;
    threadMetadata: IThreadMetadataState;
}
