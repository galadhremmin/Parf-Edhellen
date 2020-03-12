import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import IGlossResourceApi, {
    IGetGlossResponse,
} from './IGlossResourceApi';

export default class GlossResourceApiConnector implements IGlossResourceApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public delete(glossId: number, replacementId: number) {
        return this._api.delete<void>(`gloss/${glossId}`, { replacementId });
    }

    public async gloss(glossId: number) {
        const response = await this._api.get<IGetGlossResponse>(`gloss/${glossId}`);
        return response.gloss;
    }
}
