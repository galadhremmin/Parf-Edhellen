import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import IDiscussApi, {
    ICreatePostRequest,
    ICreatePostResponse,
    IDeletePostRequest,
    ILikePostRequest,
    ILikePostResponse,
    IPostRequest,
    IPostResponse,
    IStickThreadRequest,
    IStickThreadResponse,
    IThreadMetadataRequest,
    IThreadMetadataResponse,
    IThreadRequest,
    IThreadResponse,
    IUpdatePostRequest,
    IUpdatePostResponse,
} from './IDiscussApi';

export default class DiscussApiConnector implements IDiscussApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public thread(payload: IThreadRequest) {
        const params: Partial<IThreadRequest> = {};

        if (payload.offset !== undefined) {
            params.offset = payload.offset;
        }

        if (payload.forumPostId !== undefined) {
            params.forumPostId = payload.forumPostId;
        }

        return this._api.get<IThreadResponse>(
            this._makePath('thread/' +
                (payload.id || `${payload.entityType}/${payload.entityId}`),
            ),
            params,
        );
    }

    public threadMetadata(payload: IThreadMetadataRequest) {
        return this._api.post<IThreadMetadataResponse>(
            this._makePath('thread/metadata'), payload,
        );
    }

    public post(payload: IPostRequest) {
        return this._api.get<IPostResponse>(
            this._makePath(`post/${payload.forumPostId}`),
            {
                includeDeleted: payload.includeDeleted ? 1 : 0,
                markdown: payload.markdown ? 1 : 0,
            },
        );
    }

    public createPost(payload: ICreatePostRequest) {
        return this._api.post<ICreatePostResponse>(
            this._makePath('post'),
            payload,
        );
    }

    public deletePost(payload: IDeletePostRequest) {
        return this._api.delete<void>(
            this._makePath(`post/${payload.forumPostId}`),
        );
    }

    public updatePost(payload: IUpdatePostRequest) {
        return this._api.put<IUpdatePostResponse>(
            this._makePath(`post/${payload.forumPostId}`),
            payload,
        );
    }

    public likePost(payload: ILikePostRequest) {
        return this._api.post<ILikePostResponse>(
            this._makePath('like'),
            payload,
        );
    }

    public stickThread(payload: IStickThreadRequest) {
        return this._api.put<IStickThreadResponse>(
            this._makePath('thread/stick'),
            payload,
        );
    }

    private _makePath(path: string) {
        return `discuss/${path}`;
    }
}
