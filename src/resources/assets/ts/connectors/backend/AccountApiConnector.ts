import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    FindResponse,
    IFindRequest,
} from './AccountApiConnector._types';

export default class AccountApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public find(args: IFindRequest) {
        return this._api.value.post<FindResponse>('account/find', args);
    }
}
