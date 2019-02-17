import { IAccountEntity } from './BookApiConnector._types';

export interface IThreadRequest {
    entityId?: number;
    entityType: string;
    id: number;
    offset?: number;
}

export interface IThreadResponse {
    context: never;
    currentPage: number;
    pages: Array<string | number>;
    noOfPages: number;
    posts: IPostEntity[];
    thread: IThreadEntity;
    threadId: number;
}

export interface ICreateRequest {
    content: string;
    entityId?: number;
    entityType?: number;
    forumGroupId?: number;
    isSticky?: boolean;
    parentFormPostId?: number;
    subject: string;
}

export interface ICreateResponse {
    notSureYet: never;
}

export interface IThreadEntity {
    accountId: number;
    createdAt: string;
    entityId: number;
    entityType: string;
    forumGroupId: number;
    id: number;
    isSticky: boolean;
    normalizedSubject: string;
    numberOfLikes: number;
    numberOfPosts: number;
    subject: string;
    updatedAt: string;
}

export interface IPostEntity {
    account: IAccountEntity;
    content: string;
    createdAt: string;
    id: number;
    isDeleted: boolean;
    numberOfLikes?: number;
    parentForumPostId: number;
}
