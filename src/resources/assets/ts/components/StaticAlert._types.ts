import { ReactNode } from 'react';
import { ComponentEventHandler } from './Component._types';

export interface IProps {
    children: ReactNode;
    dismissable?: boolean;
    onDismiss?: ComponentEventHandler<void>;
    type?: 'success' | 'info' | 'warning' | 'danger';
}
