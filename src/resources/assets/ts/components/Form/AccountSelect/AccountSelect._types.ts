
import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

import { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps<IAccountSuggestion> {
    apiConnector?: AccountApiConnector;
}
