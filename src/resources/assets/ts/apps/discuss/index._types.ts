import {
    ComponentEventHandler,
} from '@root/components/Component._types';
import {
    IPostEntity,
    IThreadEntity,
} from '@root/connectors/backend/DiscussApiConnector._types';

export interface IProps {
    currentPage?: number;
    noOfPages?: number;
    pages?: Array<string | number>;
    onPageChange?: ComponentEventHandler<IPageChangeEvent>;
    posts?: IPostEntity[];
    thread?: IThreadEntity;
}

interface IPageChangeEvent {
    pageNumber: number;
    thread: IThreadEntity;
}
