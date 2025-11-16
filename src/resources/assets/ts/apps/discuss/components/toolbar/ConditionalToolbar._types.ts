import type { IRoleManager } from '@root/security';

import type { IProps as IToolbarProps } from './index._types';

export interface IProps extends IToolbarProps {
    roleManager?: IRoleManager;
}
