import type { ReactNode } from 'react';

export interface IProps {
    backgroundImageUrl?: string;
    backgroundMobileImageUrl?: string;
    children: ReactNode;
    className?: string;
}
