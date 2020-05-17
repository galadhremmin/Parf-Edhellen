import {
    ComponentEventHandler,
} from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/IDiscussApi';

import { IFormChangeData } from '../components/Form._types';
import { IThreadMetadataArgs } from '../components/toolbar/index._types';
import { IThreadMetadataState } from '../reducers/ThreadMetadataReducer._types';
import { ICreatePostAction } from '../reducers/ThreadReducer._types';

export interface IPageChangeEvent {
    pageNumber: number;
    thread: IThreadEntity;
}

export interface IProps {
    currentPage?: number;
    jumpPostId?: number;
    newPostContent?: string;
    newPostEnabled?: boolean;
    newPostLoading?: boolean;
    noOfPages?: number;
    pages?: (string | number)[];
    onExistingPostChange?: ComponentEventHandler<number>;
    onExistingThreadChange?: ComponentEventHandler<number>;
    onExistingThreadMetadataChange?: ComponentEventHandler<IThreadMetadataArgs>;
    onNewPostChange?: ComponentEventHandler<IFormChangeData>;
    onNewPostCreate?: ComponentEventHandler<void>;
    onNewPostSubmit?: ComponentEventHandler<ICreatePostAction>;
    onNewPostDiscard?: ComponentEventHandler<void>;
    onPageChange?: ComponentEventHandler<IPageChangeEvent>;
    onReferenceLinkClick?: ComponentEventHandler<IReferenceLinkClickDetails>;
    posts?: IPostEntity[];
    thread?: IThreadEntity;
    threadMetadata?: IThreadMetadataState;
    threadPostId?: number;
}
