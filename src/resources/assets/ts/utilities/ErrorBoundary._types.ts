import { IReportErrorApi } from '@root/connectors/IReportErrorApi';

export interface IProps {
    children: React.ReactNode;
    reportErrorApi?: IReportErrorApi;
}

export interface IState {
    healthy: boolean;
}
