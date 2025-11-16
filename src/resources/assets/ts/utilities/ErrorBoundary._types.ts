import type { ReactNode } from 'react';
import type IGlobalEvents from '@root/connectors/IGlobalEvents';
import type { IReportErrorApi } from '@root/connectors/IReportErrorApi';

export interface IProps {
    children: ReactNode;
    reportErrorApi?: IReportErrorApi;
    globalEvents?: IGlobalEvents;
}

export interface IState {
    healthy: boolean;
}
