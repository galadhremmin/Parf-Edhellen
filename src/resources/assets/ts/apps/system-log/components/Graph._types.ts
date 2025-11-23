import type {
    IErrorsByWeek,
    IWeeklyErrors,
    IWeeklyFailedJobs,
} from '../index._types';

export interface IProps {
    categories: string[];
    data: IErrorsByWeek<IWeeklyErrors>[] | IWeeklyFailedJobs[];
    onCategoryClick?: (category: string, week: string, year?: number, weekNumber?: number) => void;
}
