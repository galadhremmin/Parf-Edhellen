import type IBookApi from '@root/connectors/backend/IBookApi';

import type { IProps as IAsyncSelectProps } from './AsyncSelect/AsyncSelect._types';
import type { IComponentProps } from './FormComponent._types';

export interface IProps extends IComponentProps<number>, Partial<Pick<IAsyncSelectProps, 'allowEmpty' | 'emptyText'>> {
    apiConnector?: IBookApi;
}
