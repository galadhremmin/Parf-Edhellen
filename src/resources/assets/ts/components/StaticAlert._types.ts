import { ReactNode } from 'react';

export interface IProps {
    children: ReactNode;
    type?: 'success' | 'info' | 'warning' | 'danger';
}
