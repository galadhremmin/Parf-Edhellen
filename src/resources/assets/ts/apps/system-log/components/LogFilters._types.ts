import type { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';
import type { ILogApi } from '@root/connectors/backend/ILogApi';

export interface ILogFilter {
    account: IAccountSuggestion | null;
    ip: string | null;
}

export interface IProps {
    logApi: ILogApi;
    filter: ILogFilter;
    onChange: (filter: ILogFilter) => void;
}
