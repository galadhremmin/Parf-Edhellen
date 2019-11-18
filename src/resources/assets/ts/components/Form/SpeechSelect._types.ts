import ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

import { IComponentProps } from './FormComponent._types';

export interface IProps extends IComponentProps<ISpeechEntity | number> {
    apiConnector?: ISpeechResourceApi;
}
