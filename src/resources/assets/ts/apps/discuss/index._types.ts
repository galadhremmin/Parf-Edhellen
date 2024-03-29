import { IProps as IDiscussProps } from './containers/Discuss._types';

export interface IProps extends Partial<IDiscussProps> {
    jumpEnabled?: boolean;
    prefetched?: boolean;
    readonly?: boolean;
}
