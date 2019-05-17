import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    FindResponse,
    IFindRequest,

    IGetAvatarRequest,
    IGetAvatarResponse,
} from './AccountApiConnector._types';

export default class AccountApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public find(args: IFindRequest) {
        return this._api.value.post<FindResponse>('account/find', args);
    }

    public getAvatar(args: IGetAvatarRequest) {
        return this._api.value.get<IGetAvatarResponse>(`account/${args.accountId}/avatar`);
    }
}
