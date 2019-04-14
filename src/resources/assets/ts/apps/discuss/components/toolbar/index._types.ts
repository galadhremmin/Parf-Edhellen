import { ComponentEventHandler } from '@root/components/Component._types';
import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/DiscussApiConnector._types';

import { IThreadMetadataState } from '../../reducers/ThreadMetadataReducer._types';

export interface IThreadMetadataArgs {
    forumPostId: number[];
    forumThreadId: number;
}

export interface IProps {
    apiConnector?: DiscussApiConnector;
    onPostChange?: ComponentEventHandler<number>;
    onThreadMetadataChange?: ComponentEventHandler<IThreadMetadataArgs>;
    post: IPostEntity;
    thread: IThreadEntity;
    threadMetadata: IThreadMetadataState;
}
