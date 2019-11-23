import IRoleManager from '@root/security/IRoleManager';
import { IProps as IRootProps } from '../index._types';

export interface IProps extends IRootProps {
    roleManager?: IRoleManager;
}
