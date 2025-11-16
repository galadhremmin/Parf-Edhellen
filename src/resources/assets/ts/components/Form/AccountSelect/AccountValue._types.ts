import type IAccountApi from '@root/connectors/backend/IAccountApi';
import type { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';

export interface IProps {
    account: IAccountSuggestion;
    apiConnector?: IAccountApi;
}
