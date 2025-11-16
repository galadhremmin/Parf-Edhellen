import type { ReactNode } from 'react';

import type { IProps as IconProps } from '@root/components/TextIcon._types';
import type { IProps as IOriginalProps } from './index._types';
import type { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps extends IOriginalProps {
    children: ReactNode;
    icon: IconProps['icon'];
    onClick?: ComponentEventHandler<void>;
}
