import {
    ComponentEventHandler,
} from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/IDiscussApi';
import { IRoleManager } from '@root/security';

import { IFormChangeData } from '../components/Form._types';
import { IThreadMetadataArgs } from '../components/toolbar/index._types';
import { IThreadMetadataState } from '../reducers/ThreadMetadataReducer._types';
import { ICreatePostAction } from '../reducers/ThreadReducer._types';

export interface IPageChangeEvent {
    pageNumber: number;
    thread: IThreadEntity;
}

export interface IThreadActivity {
    forumThreadId: number;
    change?: IFormChangeData;
}

export interface IProps {
    currentPage?: number;
    entityId?: number;
    entityType?: string;
    jumpPostId?: number;
    newPostContent?: string;
    newPostEnabled?: boolean;
    noOfPages?: number;
    pages?: (string | number)[];
    onExistingPostChange?: ComponentEventHandler<number>;
    onExistingThreadChange?: ComponentEventHandler<number>;
    onExistingThreadMetadataChange?: ComponentEventHandler<IThreadMetadataArgs>;
    onNewPostChange?: ComponentEventHandler<IThreadActivity>;
    onNewPostCreate?: ComponentEventHandler<IThreadActivity>;
    onNewPostSubmit?: ComponentEventHandler<ICreatePostAction>;
    onNewPostDiscard?: ComponentEventHandler<IThreadActivity>;
    onPageChange?: ComponentEventHandler<IPageChangeEvent>;
    onReferenceLinkClick?: ComponentEventHandler<IReferenceLinkClickDetails>;
    posts?: IPostEntity[];
    readonly?: boolean;
    roleManager?: IRoleManager;
    thread?: IThreadEntity;
    threadMetadata?: IThreadMetadataState;
    threadPostId?: number;
}
