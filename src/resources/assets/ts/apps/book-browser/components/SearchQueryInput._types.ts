import { IComponentEvent } from '@root/components/Component._types';

export interface IComponentProps {
    autoFocus?: boolean;
}

export interface IProps extends IComponentProps {
    loading?: boolean;
    onSearchResultNavigate?: (ev: IComponentEvent<number>) => void;
}
