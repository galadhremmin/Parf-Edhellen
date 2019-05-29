import SpeechResourceApiConnector from '@root/connectors/backend/SpeechResourceApiConnector';
import { ISpeechEntity } from '@root/connectors/backend/SpeechResourceApiConnector._types';

import { IComponentProps } from './FormComponent._types';

export interface IProps extends IComponentProps<ISpeechEntity | number> {
    apiConnector: SpeechResourceApiConnector;
}
