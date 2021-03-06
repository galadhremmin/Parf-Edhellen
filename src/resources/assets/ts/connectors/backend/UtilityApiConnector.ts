import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';

import { IGetErrorsResponse } from './ILogApi';
import IUtilityApi, {
    IMarkdownParserRequest,
    IMarkdownParserResponse,
} from './IUtilityApi';

export default class UtilityApiConnector implements IUtilityApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi)) {
    }

    public getErrors(page: number = 1) {
        page = Math.max(1, page);
        return this._api.get<IGetErrorsResponse>('utility/errors', { page });
    }

    public parseMarkdown(args: IMarkdownParserRequest) {
        return this._api.post<IMarkdownParserResponse>('utility/markdown', args);
    }
}
