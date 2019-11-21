import SharedReference from '../../utilities/SharedReference';
import ApiConnector from '../ApiConnector';

import IUtilityApi, {
    IGetErrorsResponse,
    IMarkdownParserRequest,
    IMarkdownParserResponse,
} from './IUtilityApi';

export default class UtilityApiConnector implements IUtilityApi {
    constructor(private _api = new SharedReference(ApiConnector)) {
    }

    public getErrors() {
        return this._api.value.get<IGetErrorsResponse>('utility/errors');
    }

    public parseMarkdown(args: IMarkdownParserRequest) {
        return this._api.value.post<IMarkdownParserResponse>('utility/markdown', args);
    }
}
