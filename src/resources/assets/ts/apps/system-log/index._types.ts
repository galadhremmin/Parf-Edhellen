import type { ILogApi } from '@root/connectors/backend/ILogApi';

export interface IWeeklyErrors {
    [errorCategory: string]: number;
}

export interface IWeeklyFailedJobs {
    year: number;
    numberOfErrors: number;
    week: number;
}

export interface IErrorsByWeek<T> {
    [yearWeek: string]: T;
}

export interface IViewsPerDay {
    date: string;
    count: number;
}

export interface IProps {
    errorsByWeek: IErrorsByWeek<IWeeklyErrors>[];
    errorCategories: string[];
    logApi: ILogApi;
    viewsPerDay?: IViewsPerDay[];
}
