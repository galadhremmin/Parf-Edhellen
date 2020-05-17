import { IProps as IDiscussProps } from './containers/Discuss._types';

export interface IProps extends Partial<IDiscussProps> {
    entityId?: number;
    entityType?: string;
    jumpEnabled?: boolean;
    prefetched?: boolean;
}
