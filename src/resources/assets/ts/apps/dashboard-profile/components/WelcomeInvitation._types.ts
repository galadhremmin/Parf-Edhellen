import type { IProps as ITextIconProps } from '@root/components/TextIcon._types';

import type { IWelcomeStep } from '../index._types';

export interface IProps {
    step: IWelcomeStep;
    icon?: ITextIconProps['icon'];
}
