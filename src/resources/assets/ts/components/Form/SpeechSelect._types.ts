import ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

import { IProps as IAsyncSelectProps } from './AsyncSelect/AsyncSelect._types';
import { IComponentProps } from './FormComponent._types';

export interface IProps extends IComponentProps<ISpeechEntity | number>, Partial<Pick<IAsyncSelectProps, 'allowEmpty' | 'emptyText'>> {
    apiConnector?: ISpeechResourceApi;
}
