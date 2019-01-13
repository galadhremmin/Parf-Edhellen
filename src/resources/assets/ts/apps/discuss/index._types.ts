import { IPostEntity } from '@root/connectors/backend/ForumApiConnector._types';

export interface IPaginatedPosts {
    currentPage: number;
    noOfPages: number;
    pages: string[];
    posts?: IPostEntity[];
    threadId: number;
}

export interface IProps {
    entityId: number;
    entityType: string;
    discussData: IPaginatedPosts;
}
