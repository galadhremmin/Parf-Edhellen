import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import {
    IInflectionMap,
    IInflectionResourceApi,
} from './IInflectionResourceApi';

export default class InflectionResourceApiConnector implements IInflectionResourceApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public inflections() {
        return this._api.get<IInflectionMap>('inflection');
    }
}
