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

    public getErrors(from: number = 0, to: number = 100, category?: string) {
        const params: { from: number; to: number; category?: string } = { from, to };
        if (category !== undefined) {
            params.category = category;
        }
        return this._api.get<IGetErrorsResponse>('utility/errors', params);
    }

    public getFailedJobs(from: number = 0, to: number = 100) {
        return this._api.get<IGetFailedJobsResponse>('utility/failed-jobs', { from, to });
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
}
