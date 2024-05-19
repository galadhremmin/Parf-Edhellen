import React, { ReactNode } from 'react';

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
    title?: string;
    titleButton?: React.ReactNode;
    type?: PanelType;
}
