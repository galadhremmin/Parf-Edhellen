import type { ReactNode, DOMAttributes } from 'react';

export interface IProps extends Partial<DOMAttributes<HTMLDivElement>> {
    children?: ReactNode;
    path?: string;
    title?: string;
}
