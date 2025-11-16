import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type IAccountApi from './IAccountApi';
import type {
    FindResponse,
    IFindRequest,
    IGetAvatarRequest,
    IGetAvatarResponse,
    IGetFeatureBackgroundsResponse,
    IGetFeedRequest,
    IGetFeedResponse,
    ISaveAvatarRequest,
    ISaveAvatarResponse,
    ISaveFeatureBackgroundRequest,
    ISaveFeatureBackgroundResponse,
    ISaveProfileRequest,
    ISaveProfileResponse,
} from './IAccountApi';

export default class AccountApiConnector implements IAccountApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public find(args: IFindRequest) {
        return this._api.post<FindResponse>('account/find', args);
    }

    public getAvatar(args: IGetAvatarRequest) {
        return this._api.get<IGetAvatarResponse>(`account/${args.accountId}/avatar`);
    }

    public getFeed(args: IGetFeedRequest): Promise<IGetFeedResponse> {
        return this._api.get<IGetFeedResponse>(`account/${args.accountId}/feed` + (args.cursor ? `?cursor=${encodeURIComponent(args.cursor)}` : ''));
    }

    public getFeatureBackgrounds() {
        return this._api.get<IGetFeatureBackgroundsResponse>('account/backgrounds');
    }

    public saveAvatar(args: ISaveAvatarRequest) {
        const formData = new FormData();
        formData.append('avatar', args.file, args.file.name);

        return this._api.post<ISaveAvatarResponse>(`account/avatar/edit/${args.accountId}`, formData);
    }

    public saveFeatureBackground(args: ISaveFeatureBackgroundRequest): Promise<ISaveFeatureBackgroundResponse> {
        const formData = {
            featureBackgroundUrl: args.featureBackgroundUrl,
        };
        return this._api.put<ISaveFeatureBackgroundRequest>(`account/background/edit/${args.accountId}`, formData);
    }

    public saveProfile(args: ISaveProfileRequest) {
        return this._api.post<ISaveProfileResponse>(`account/edit/${args.accountId}`, args);
    }
}
