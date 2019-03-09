import { IAccountEntity } from './BookApiConnector._types';

export interface IThreadRequest {
    entityId?: number;
    entityType: string;
    forumPostId?: number;
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
    entityType?: string;
    forumGroupId?: number;
    forumThreadId?: number;
    isSticky?: boolean;
    parentFormPostId?: number;
    subject?: string;
}

export interface ICreateResponse {
    post: IPostEntity;
    thread: IThreadEntity;
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

    _focused?: boolean;
}
