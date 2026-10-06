import type IAccountApi from '@root/connectors/backend/IAccountApi';
import type IRoleManager from '@root/security/IRoleManager';
import type { IProps as IRootProps } from '../index._types';

export interface IProps extends IRootProps {
    accountApi?: IAccountApi;
    roleManager?: IRoleManager;
}
