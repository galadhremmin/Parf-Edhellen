import { ReactNode } from 'react';
import { ComponentEventHandler } from './Component._types';

export interface IProps<V> {
    actionBar?: boolean;
    cancelButtonText?: string;
    children: React.ReactNode;
    confirmButtonText?: string;
    dismissable?: boolean;
    onConfirm?: ComponentEventHandler<V>;
    onDismiss?: ComponentEventHandler<void>;
    open: boolean;
    size?: 'sm' | 'lg' | 'xl' | undefined;
    title: ReactNode;
    valid?: boolean;
    value?: V;
}
