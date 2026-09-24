import type { IProps as IDiscussProps } from './containers/Discuss._types';

export interface IProps extends Partial<IDiscussProps> {
    historyEnabled?: boolean;
    jumpEnabled?: boolean;
    prefetched?: boolean;
    readonly?: boolean;
}
