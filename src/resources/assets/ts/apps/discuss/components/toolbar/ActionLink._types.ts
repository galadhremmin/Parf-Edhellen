import { ReactNode } from 'react';
import { IProps as IOriginalProps } from './index._types';

export interface IProps extends IOriginalProps {
    children: ReactNode;
    icon: string;
    onClick?: () => void;
}
