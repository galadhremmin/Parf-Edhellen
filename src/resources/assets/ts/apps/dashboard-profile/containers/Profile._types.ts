import type IRoleManager from '@root/security/IRoleManager';
import type { IProps as IRootProps } from '../index._types';

export interface IProps extends IRootProps {
    roleManager?: IRoleManager;
}
