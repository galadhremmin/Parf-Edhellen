import { IAccountEntity } from './BookApiConnector._types';

export interface IThreadRequest {
    entityId?: number;
    entityType: string;
    forumPostId?: number;
    id: number;
    offset?: number;
}

export interface IThreadResponse {
    currentPage: number;
    jumpPostId: number;
    pages: Array<string | number>;
    noOfPages: number;
    posts: IPostEntity[];
    thread: IThreadEntity;
    threadId: number;
    threadPostId: number;
}

export interface IThreadMetadataRequest {
    forumThreadId: number;
    forumPostId: number[];
}

export interface IThreadMetadataResponse {
    forumPostId: number[];
    likes: number[];
    likesPerPost: {
        [postId: number]: number;
    };
}

export interface ICreatePostRequest {
    content: string;
    entityId?: number;
    entityType?: string;
    forumGroupId?: number;
    forumThreadId?: number;
    isSticky?: boolean;
    parentFormPostId?: number;
    subject?: string;
}

export interface ICreatePostResponse {
    post: IPostEntity;
    thread: IThreadEntity;
}

export interface IDeletePostRequest {
    forumPostId: number;
}

export interface IDeletePostResponse {
    dummy: never;
}

export interface IUpdatePostRequest {
    content: string;
    forumPostId: number;
    isSticky?: boolean;
    subject?: string;
}

export interface IUpdatePostResponse {
    post: IPostEntity;
    thread: IThreadEntity;
}

export interface ILikePostRequest {
    forumPostId: number;
}

export interface ILikePostResponse {
    like: ILikeEntity;
}

export interface IPostRequest {
    forumPostId: number;
}

export interface IPostResponse {
    post: IPostEntity;
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

    _isFocused?: boolean;
    _isThreadPost?: boolean;
}

export interface ILikeEntity {
    accountId: number;
    createdAt: string;
    forumPostId: number;
    id: number;
    updatedAt: string;
}
