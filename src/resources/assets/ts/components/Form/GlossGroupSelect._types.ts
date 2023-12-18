import IBookApi from '@root/connectors/backend/IBookApi';

import { IProps as IAsyncSelectProps } from './AsyncSelect/AsyncSelect._types';
import { IComponentProps } from './FormComponent._types';

export interface IProps extends IComponentProps<number>, Partial<Pick<IAsyncSelectProps, 'allowEmpty' | 'emptyText'>> {
    apiConnector?: IBookApi;
}
