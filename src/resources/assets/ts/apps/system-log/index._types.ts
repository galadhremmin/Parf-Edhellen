import { ILogApi } from '@root/connectors/backend/ILogApi';

export interface IWeeklyErrors {
    [errorCategory: string]: number;
}

export interface IErrorsByWeek {
    [yearWeek: string]: IWeeklyErrors;
}

export interface IProps {
    errorsByWeek: IErrorsByWeek[];
    logApi: ILogApi;
}
