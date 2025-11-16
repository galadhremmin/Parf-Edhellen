import type { DetailedHTMLProps, InputHTMLAttributes } from 'react';
import type { ComponentEventHandler } from './Component._types';

export interface IProps extends DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement> {
    formGroupClassName?: string;
    onCopyActionFail?: ComponentEventHandler<any>;
    onCopyActionSuccess?: ComponentEventHandler<string | number>;
}
