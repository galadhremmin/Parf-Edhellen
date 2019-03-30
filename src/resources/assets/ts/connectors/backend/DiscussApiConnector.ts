import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    ICreateRequest,
    ICreateResponse,
    ILikeRequest,
    ILikeResponse,
    IThreadMetadataRequest,
    IThreadMetadataResponse,
    IThreadRequest,
    IThreadResponse,
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
        return this._api.value.post<IThreadMetadataResponse>(this._makePath('thread/metadata'), payload);
    }

    public create(payload: ICreateRequest) {
        return this._api.value.post<ICreateResponse>(
            this._makePath('store/post'),
            payload,
        );
    }

    public like(payload: ILikeRequest) {
        return this._api.value.post<ILikeResponse>(
            this._makePath('store/like'),
            payload,
        );
    }

    private _makePath(path: string) {
        return `discuss/${path}`;
    }
}
