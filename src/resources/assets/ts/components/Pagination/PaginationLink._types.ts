import type { ReactNode } from 'react';

import type { ComponentEventHandler } from '../Component._types';

export interface IProps {
    children?: ReactNode;
    onClick?: ComponentEventHandler<number>;
    pageNumber: string | number;
    parameterName?: string;
}
