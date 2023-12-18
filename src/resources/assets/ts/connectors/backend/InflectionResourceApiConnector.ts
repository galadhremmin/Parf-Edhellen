import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import {
    IInflectionMap,
    IInflectionResourceApi,
} from './IInflectionResourceApi';

export default class InflectionResourceApiConnector implements IInflectionResourceApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public inflections() {
        return this._api.get<IInflectionMap>('inflection');
    }
}
