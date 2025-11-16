import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type {
    IGetErrorsResponse,
    IGetFailedJobsResponse,
    ILogApi,
} from './ILogApi';
import type IUtilityApi from './IUtilityApi';
import type {
    IMarkdownParserRequest,
    IMarkdownParserResponse,
} from './IUtilityApi';

export default class UtilityApiConnector implements IUtilityApi, ILogApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public getErrors(from: number = 0, to: number = 100) {
        return this._api.get<IGetErrorsResponse>('utility/errors', { from, to });
    }

    public getFailedJobs(from: number = 0, to: number = 100) {
        return this._api.get<IGetFailedJobsResponse>('utility/failed-jobs', { from, to });
    }

    public parseMarkdown(args: IMarkdownParserRequest) {
        return this._api.post<IMarkdownParserResponse>('utility/markdown', args);
    }
}
