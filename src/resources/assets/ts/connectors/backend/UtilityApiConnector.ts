import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';

import IUtilityApi, {
    IMarkdownParserRequest,
    IMarkdownParserResponse,
} from './IUtilityApi';

export default class UtilityApiConnector implements IUtilityApi {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public parseMarkdown(args: IMarkdownParserRequest) {
        return this._api.value.post<IMarkdownParserResponse>('utility/markdown', args);
    }
}
