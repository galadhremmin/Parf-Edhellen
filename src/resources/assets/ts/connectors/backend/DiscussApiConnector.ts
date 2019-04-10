import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    ICreatePostRequest,
    ICreatePostResponse,
    IDeletePostRequest,
    ILikePostRequest,
    ILikePostResponse,
    IPostRequest,
    IPostResponse,
    IThreadMetadataRequest,
    IThreadMetadataResponse,
    IThreadRequest,
    IThreadResponse,
    IUpdatePostRequest,
    IUpdatePostResponse,
} from './DiscussApiConnector._types';

export default class DiscussApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public thread(payload: IThreadRequest) {
        const params: Partial<IThreadRequest> = {};
        if (payload.offset !== undefined) {
            params.offset = payload.offset;
        }
        if (payload.forumPostId !== undefined) {
            params.forumPostId = payload.forumPostId;
        }

        return this._api.value.get<IThreadResponse>(
            this._makePath('thread/' +
                payload.id || `resolve/${payload.entityType}/${payload.entityId}`,
            ),
            params,
        );
    }

    public threadMetadata(payload: IThreadMetadataRequest) {
        return this._api.value.post<IThreadMetadataResponse>(
            this._makePath('thread/metadata'), payload,
        );
    }

    public post(payload: IPostRequest) {
        return this._api.value.get<IPostResponse>(
            this._makePath(`post/${payload.forumPostId}`),
            {
                includeDeleted: payload.includeDeleted ? 1 : 0,
            },
        );
    }

    public createPost(payload: ICreatePostRequest) {
        return this._api.value.post<ICreatePostResponse>(
            this._makePath('post'),
            payload,
        );
    }

    public deletePost(payload: IDeletePostRequest) {
        return this._api.value.delete<void>(
            this._makePath(`post/${payload.forumPostId}`),
        );
    }

    public updatePost(payload: IUpdatePostRequest) {
        return this._api.value.put<IUpdatePostResponse>(
            this._makePath(`post/${payload.forumPostId}`),
            payload,
        );
    }

    public likePost(payload: ILikePostRequest) {
        return this._api.value.post<ILikePostResponse>(
            this._makePath('like'),
            payload,
        );
    }

    private _makePath(path: string) {
        return `discuss/${path}`;
    }
}
