import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

export interface IProps {
    account: IAccountSuggestion;
    apiConnector: AccountApiConnector;
}
