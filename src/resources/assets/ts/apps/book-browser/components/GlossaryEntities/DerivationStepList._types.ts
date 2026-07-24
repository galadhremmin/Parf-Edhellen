import type { ReactNode } from 'react';

export interface IListProps {
    children: ReactNode;
}

export interface IStepProps {
    className?: string;
    depth: number;
    children: ReactNode;
}
