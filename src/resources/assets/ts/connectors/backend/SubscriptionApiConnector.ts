import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import {
    ISubscriptionApi,
    ISubscriptionStatus,
} from './ISubscriptionApi';

export default class SubscriptionApiConnector implements ISubscriptionApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public isSubscribed(entityName: string, id: number): Promise<ISubscriptionStatus> {
        return this._api.get(SubscriptionApiConnector._makePath(entityName, id));
    }

    public subscribe(entityName: string, id: number): Promise<ISubscriptionStatus> {
        return this._api.post(SubscriptionApiConnector._makePath(entityName, id), {});
    }

    public unsubscribe(entityName: string, id: number): Promise<ISubscriptionStatus> {
        return this._api.delete(SubscriptionApiConnector._makePath(entityName, id));
    }

    private static _makePath(entityName: string, id: number) {
        return `subscription/${entityName}/${id}`;
    }
}
