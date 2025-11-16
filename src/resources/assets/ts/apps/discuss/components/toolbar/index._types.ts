import type { ComponentEventHandler } from '@root/components/Component._types';
import type IDiscussApi from '@root/connectors/backend/IDiscussApi';
import type {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/IDiscussApi';
import type { IRoleManager } from '@root/security';

import type { IThreadMetadataState } from '../../reducers/ThreadMetadataReducer._types';
import type { IThreadEntityAction } from '../../reducers/ThreadReducer._types';

export interface IThreadMetadataArgs extends IThreadEntityAction {
    forumPostId: number[];
    forumThreadId: number;
}

export interface IProps {
    apiConnector?: IDiscussApi;
    onAuthenticationRequired?: ComponentEventHandler<string>;
    onPostChange?: ComponentEventHandler<number>;
    onThreadChange?: ComponentEventHandler<number>;
    onThreadMetadataChange?: ComponentEventHandler<IThreadMetadataArgs>;
    post: IPostEntity;
    thread: IThreadEntity;
    threadMetadata: IThreadMetadataState;
    roleManager?: IRoleManager;
}
