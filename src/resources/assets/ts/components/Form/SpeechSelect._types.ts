import type ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import type { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

import type { IProps as IAsyncSelectProps } from './AsyncSelect/AsyncSelect._types';
import type { IComponentProps } from './FormComponent._types';

export interface IProps extends IComponentProps<ISpeechEntity | number>, Partial<Pick<IAsyncSelectProps, 'allowEmpty' | 'emptyText'>> {
    apiConnector?: ISpeechResourceApi;
}
