import IGlobalEvents from '@root/connectors/IGlobalEvents';
import { IReportErrorApi } from '@root/connectors/IReportErrorApi';

export interface IProps {
    children: React.ReactNode;
    reportErrorApi?: IReportErrorApi;
    globalEvents?: IGlobalEvents;
}

export interface IState {
    healthy: boolean;
}
