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
    /** A small-caps line over the title naming what the panel is about. */
    eyebrow?: ReactNode;
    /** The title's heading level; 2 when the panel is a section of the page. */
    headingLevel?: 2 | 3;
    title?: ReactNode;
    titleButton?: ReactNode;
    type?: PanelType;
}
