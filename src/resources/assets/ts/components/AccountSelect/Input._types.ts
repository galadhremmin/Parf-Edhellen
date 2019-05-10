import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

export interface IProps {
    className?: string;
    required?: boolean;
    value?: IAccountSuggestion;
}
