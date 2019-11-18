
import AccountApiConnector, { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';

import { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps<IAccountSuggestion> {
    apiConnector?: AccountApiConnector;
}
