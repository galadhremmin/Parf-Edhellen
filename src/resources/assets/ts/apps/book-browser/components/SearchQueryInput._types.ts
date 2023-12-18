import { IComponentEvent } from '@root/components/Component._types';
import { IComponentProps } from '@root/components/Form/FormComponent._types';

export interface ISearchComponentProps extends IComponentProps<string> {
    autoFocus?: boolean;
}

export interface IProps extends ISearchComponentProps {
    loading?: boolean;
    onSearchResultNavigate?: (ev: IComponentEvent<number>) => void;
}
