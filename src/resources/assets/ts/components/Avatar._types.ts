import React, { DOMAttributes } from 'react';

export interface IProps extends Partial<DOMAttributes<HTMLDivElement>> {
    children?: React.ReactNode;
    path?: string;
}
