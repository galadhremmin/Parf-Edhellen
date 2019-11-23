import { ReactNode } from 'react';
import { ComponentEventHandler } from './Component._types';

export interface IProps<V> {
    actionBar?: boolean;
    cancelButtonText?: string;
    children: React.ReactNode;
    confirmButtonText?: string;
    onConfirm?: ComponentEventHandler<V>;
    onDismiss: ComponentEventHandler<void>;
    open: boolean;
    title: ReactNode;
    valid: boolean;
    value?: V;
}
