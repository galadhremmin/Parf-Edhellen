import IAccountApi, { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';

export interface IProps {
    account: IAccountSuggestion;
    apiConnector?: IAccountApi;
}
