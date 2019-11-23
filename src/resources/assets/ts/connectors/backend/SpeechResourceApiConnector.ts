import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import ISpeechResourceApi, {
    ISpeechEntity,
} from './ISpeechResourceApi';

export default class SpeechResourceApiConnector implements ISpeechResourceApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public speeches() {
        return this._api.get<ISpeechEntity[]>('speech');
    }
}
