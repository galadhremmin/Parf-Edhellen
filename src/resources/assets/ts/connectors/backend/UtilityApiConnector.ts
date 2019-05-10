import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';

import {
    IMarkdownParserRequest,
    IMarkdownParserResponse,
} from './UtilityApiConnector._types';

export default class UtilityApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public parseMarkdown(args: IMarkdownParserRequest) {
        return this._api.value.post<IMarkdownParserResponse>('utility/markdown', args);
    }
}
