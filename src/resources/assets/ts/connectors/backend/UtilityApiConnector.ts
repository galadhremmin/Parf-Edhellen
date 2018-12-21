import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';

import {
    IMarkdownParserRequest,
    IMarkdownParserResponse,
} from './UtilityApiConnector._types';

export default class UtilityApiConnector {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public async parseMarkdown(args: IMarkdownParserRequest) {
        return await this._api.value.post<IMarkdownParserResponse>('utility/markdown', args);
    }
}
