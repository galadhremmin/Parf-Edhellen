import { IAccountEntity } from './IBookApi';

export interface IThreadRequest {
    entityId?: number;
    entityType?: string;
    forumPostId?: number;
    id?: number;
    offset?: number;
}

export interface IThreadResponse {
    currentPage: number;
    jumpPostId?: number;
    pages: (string | number)[];
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
    postUrl: string;
    thread: IThreadEntity;
}

export interface IDeletePostRequest {
    forumPostId: number;
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
    includeDeleted?: boolean;
    markdown?: boolean;
}

export interface IPostResponse {
    post: IPostEntity;
}

export interface IStickThreadRequest {
    forumThreadId: number;
    sticky: boolean;
}

export interface IStickThreadResponse {
    sticky: boolean;
}

export interface IForumGroupEntity {
    description: string;
    id: number;
    name: string;
}

export interface IThreadEntity {
    accountId: number;
    accountName?: string;
    accountAvatarPath?: string;
    accountPath?: string;
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
    threadPath?: string;
    updatedAt: string;
}

export interface IPostEntity {
    account: IAccountEntity;
    content: string;
    createdAt: string;
    forumThread?: IThreadEntity;
    forumThreadId?: number;
    id: number;
    isDeleted?: boolean;
    numberOfLikes?: number;
    parentForumPostId?: number;

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

export default interface IDiscussApi {
    thread(payload: IThreadRequest): Promise<IThreadResponse>;
    threadMetadata(payload: IThreadMetadataRequest): Promise<IThreadMetadataResponse>;
    post(payload: IPostRequest): Promise<IPostResponse>;
    createPost(payload: ICreatePostRequest): Promise<ICreatePostResponse>;
    deletePost(payload: IDeletePostRequest): Promise<void>;
    updatePost(payload: IUpdatePostRequest): Promise<IUpdatePostResponse>;
    likePost(payload: ILikePostRequest): Promise<ILikePostResponse>;
    stickThread(payload: IStickThreadRequest): Promise<IStickThreadResponse>;
}
