import { ReactNode } from 'react';

import { IProps as IconProps } from '@root/components/TextIcon._types';
import { IProps as IOriginalProps } from './index._types';

export interface IProps extends IOriginalProps {
    children: ReactNode;
    icon: IconProps['icon'];
    onClick?: () => void;
}
