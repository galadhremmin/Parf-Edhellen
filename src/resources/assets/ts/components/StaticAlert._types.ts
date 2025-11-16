import type { ReactNode } from 'react';
import type { ComponentEventHandler } from './Component._types';

export interface IProps {
    children: ReactNode;
    className?: string;
    dismissable?: boolean;
    onDismiss?: ComponentEventHandler<void>;
    type?: 'success' | 'info' | 'warning' | 'danger';
}
