import { ComponentEventHandler } from '@root/components/Component._types';
import IDiscussApi, {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/IDiscussApi';
import { IRoleManager } from '@root/security';

import { IThreadMetadataState } from '../../reducers/ThreadMetadataReducer._types';

export interface IThreadMetadataArgs {
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
