
import { IComponentProps } from '@root/components/FormComponent._types';
import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

export interface IProps extends IComponentProps<IAccountSuggestion> {
    apiConnector?: AccountApiConnector;
}
