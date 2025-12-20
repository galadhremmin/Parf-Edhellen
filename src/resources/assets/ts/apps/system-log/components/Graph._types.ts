import type { ComponentEventHandler } from '@root/components/Component._types';
import type {
    IErrorsByWeek,
    IWeeklyErrors,
    IWeeklyFailedJobs,
} from '../index._types';

export interface IProps {
    categories: string[];
    data: IErrorsByWeek<IWeeklyErrors>[] | IWeeklyFailedJobs[];
    onCategoryClick?: ComponentEventHandler<{ category: string, week: string, year?: number, weekNumber?: number }>;
}
