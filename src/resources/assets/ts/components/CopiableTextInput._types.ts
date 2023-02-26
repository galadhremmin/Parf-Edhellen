import { ComponentEventHandler } from './Component._types';

export interface IProps extends React.DetailedHTMLProps<React.InputHTMLAttributes<HTMLInputElement>, HTMLInputElement> {
    formGroupClassName?: string;
    onCopyActionFail?: ComponentEventHandler<any>;
    onCopyActionSuccess?: ComponentEventHandler<string | number>;
}
