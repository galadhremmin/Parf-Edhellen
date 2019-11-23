import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import ISpeechResourceApi, {
    ISpeechEntity,
} from './ISpeechResourceApi';

export default class SpeechResourceApiConnector implements ISpeechResourceApi {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public speeches() {
        return this._api.value.get<ISpeechEntity[]>('speech');
    }
}
