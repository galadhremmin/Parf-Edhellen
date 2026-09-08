import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type {
    IGetAccountIpHistoryResponse,
    IGetErrorsRequest,
    IGetErrorsResponse,
    IGetFailedJobsRequest,
    IGridFilterModel,
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

    public getErrors(args: IGetErrorsRequest = {}) {
        const { offset = 0, limit = 100, category, accountId, ip, filters } = args;
        const params: {
            offset: number;
            limit: number;
            category?: string;
            accountId?: number;
            ip?: string;
            filters?: IGridFilterModel;
        } = { offset, limit };
        if (category !== undefined) {
            params.category = category;
        }
        if (accountId !== undefined) {
            params.accountId = accountId;
        }
        if (ip !== undefined) {
            params.ip = ip;
        }
        if (filters !== undefined && Object.keys(filters).length > 0) {
            params.filters = filters;
        }
        return this._api.get<IGetErrorsResponse>('utility/errors', params);
    }

    public getAccountIpHistory(accountId: number) {
        return this._api.get<IGetAccountIpHistoryResponse>(`utility/account/${accountId}/ip-history`);
    }

    public getFailedJobs(args: IGetFailedJobsRequest = {}) {
        const { offset = 0, limit = 100 } = args;
        return this._api.get<IGetFailedJobsResponse>('utility/failed-jobs', { offset, limit });
    }

    public deleteError(id: number) {
        return this._api.delete<void>(`utility/error/${id}`);
    }

    public deleteErrorsByCategory(category: string, year?: number, week?: number) {
        const params: { category: string; year?: number; week?: number } = { category };
        if (year !== undefined) {
            params.year = year;
        }
        if (week !== undefined) {
            params.week = week;
        }
        return this._api.delete<{ deleted: number }>('utility/errors/category', params);
    }

    public parseMarkdown(args: IMarkdownParserRequest) {
        return this._api.post<IMarkdownParserResponse>('utility/markdown', args);
    }

    public reportMissingWord(word: string) {
        return this._api.post<void>('utility/missing-word', { word });
    }
}
