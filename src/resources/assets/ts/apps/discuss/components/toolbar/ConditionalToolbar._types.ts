import { IRoleManager } from '@root/security';

import { IProps as IToolbarProps } from './index._types';

export interface IProps extends IToolbarProps {
    roleManager?: IRoleManager;
}
