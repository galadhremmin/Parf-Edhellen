import type IAccountApi from '@root/connectors/backend/IAccountApi';
import type { IProps as IRootProps } from '../index._types';

export interface IProps extends IRootProps {
    api: IAccountApi;
}
