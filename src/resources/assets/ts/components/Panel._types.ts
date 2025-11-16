import type { ReactNode } from 'react';

export const enum PanelType {
    Danger = 'danger',
    Default = 'default',
    Info = 'info',
    Primary = 'primary',
    Success = 'success',
    Warning = 'warning',
}

export interface IProps {
    children?: ReactNode;
    className?: string;
    title?: string;
    titleButton?: ReactNode;
    type?: PanelType;
    shadow?: boolean;
}
