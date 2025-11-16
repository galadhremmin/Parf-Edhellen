
import type AccountApiConnector from '@root/connectors/backend/IAccountApi';
import type { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';

import type { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps<IAccountSuggestion> {
    apiConnector?: AccountApiConnector;
}
