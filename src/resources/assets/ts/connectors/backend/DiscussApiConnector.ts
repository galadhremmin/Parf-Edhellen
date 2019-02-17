import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    ICreateRequest,
    ICreateResponse,
    IThreadRequest,
    IThreadResponse,
} from './DiscussApiConnector._types';

export default class DiscussApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public thread(payload: IThreadRequest) {
        return this._api.value.get<IThreadResponse>(
            this._makePath('thread/' +
                payload.id || `resolve/${payload.entityType}/${payload.entityId}`,
            ) + `?offset=${payload.offset || 0}`,
        );
    }

    public create(payload: ICreateRequest) {
        return this._api.value.post<ICreateResponse>(
            this._makePath('store'),
            payload,
        );
    }

    private _makePath(path: string) {
        return `discuss/${path}`;
    }
}
