import {
    ComponentEventHandler,
} from '@root/components/Component._types';
import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/DiscussApiConnector._types';

import { ICreatePostAction } from './reducers/ThreadReducer._types';

export interface IProps {
    currentPage?: number;
    noOfPages?: number;
    pages?: Array<string | number>;
    onPageChange?: ComponentEventHandler<IPageChangeEvent>;
    onPostSubmit?: ComponentEventHandler<ICreatePostAction>;
    posts?: IPostEntity[];
    thread: IThreadEntity;
}

interface IPageChangeEvent {
    pageNumber: number;
    thread: IThreadEntity;
}

