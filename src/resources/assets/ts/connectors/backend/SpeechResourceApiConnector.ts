import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type ISpeechResourceApi from './ISpeechResourceApi';
import type {
    ISpeechEntity,
} from './ISpeechResourceApi';

export default class SpeechResourceApiConnector implements ISpeechResourceApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public speeches() {
        return this._api.get<ISpeechEntity[]>('speech');
    }
}
