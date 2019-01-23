import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    IThreadRequest,
    IThreadResponse,
} from './DiscussApiConnector._types';

export default class FlashcardApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public thread(args: IThreadRequest) {
        return this._api.value.get<IThreadResponse>(
            this._makePath(`thread/${args.id}?offset=${args.offset || 0}`),
        );
    }

    private _makePath(path: string) {
        return `/discuss/${path}`;
    }
}
