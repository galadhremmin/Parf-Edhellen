import { ReactNode } from 'react';
import { ComponentEventHandler } from './Component._types';

export interface IProps<V> {
    actionBar?: boolean;
    children: React.ReactNode;
    onConfirm?: ComponentEventHandler<V>;
    onDismiss: ComponentEventHandler<void>;
    open: boolean;
    title: ReactNode;
    valid: boolean;
    value?: V;
}
