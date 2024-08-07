import IAccountApi from '@root/connectors/backend/IAccountApi';
import { IProps as IRootProps } from '../../index._types';

export interface IProps extends Pick<IRootProps, 'account'> {
    accountApi?: IAccountApi;
}