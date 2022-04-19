import {
    IErrorsByWeek,
    IWeeklyErrors,
    IWeeklyFailedJobs,
} from '../index._types';

export interface IProps {
    categories: string[];
    data: IErrorsByWeek<IWeeklyErrors>[] | IWeeklyFailedJobs[];
}
