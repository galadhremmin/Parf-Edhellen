import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';
import {
    ISpeechEntity,
} from './SpeechResourceApiConnector._types';

export default class SpeechResourceApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public speeches() {
        return this._api.value.get<ISpeechEntity[]>('speech');
    }
}
