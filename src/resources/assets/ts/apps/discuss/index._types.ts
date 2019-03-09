import {
    ComponentEventHandler,
} from '@root/components/Component._types';
import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/DiscussApiConnector._types';

import { IFormChangeData } from './components/Form._types';
import { ICreatePostAction } from './reducers/ThreadReducer._types';

export interface IProps {
    currentPage?: number;
    jumpPostId?: number;
    newPostContent?: string;
    newPostEnabled?: boolean;
    newPostLoading?: boolean;
    noOfPages?: number;
    pages?: Array<string | number>;
    onNewPostChange?: ComponentEventHandler<IFormChangeData>;
    onNewPostCreate?: ComponentEventHandler<void>;
    onNewPostSubmit?: ComponentEventHandler<ICreatePostAction>;
    onNewPostDiscard?: ComponentEventHandler<void>;
    onPageChange?: ComponentEventHandler<IPageChangeEvent>;
    posts?: IPostEntity[];
    thread: IThreadEntity;
    threadPostId?: number;
}

interface IPageChangeEvent {
    pageNumber: number;
    thread: IThreadEntity;
}
