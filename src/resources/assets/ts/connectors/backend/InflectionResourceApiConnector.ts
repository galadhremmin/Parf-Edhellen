import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type {
    IInflectionMap,
    IInflectionResourceApi,
    IAutoInflectionsRequest,
    IAutoInflectionsResponse,
} from './IInflectionResourceApi';

export default class InflectionResourceApiConnector implements IInflectionResourceApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public inflections() {
        return this._api.get<IInflectionMap>('inflection');
    }

    public autoInflections(args: IAutoInflectionsRequest) {
        return this._api.get<IAutoInflectionsResponse>(`inflection/auto/${args.lexicalEntryId}`);
    }
}
