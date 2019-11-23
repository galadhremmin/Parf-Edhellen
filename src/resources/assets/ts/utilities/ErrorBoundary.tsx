import React from 'react';

import StaticAlert from '@root/components/StaticAlert';
import { ErrorCategory } from '@root/connectors/IReportErrorApi';
import { DI, resolve } from '@root/di';

import { IProps, IState } from './ErrorBoundary._types';

export default class ErrorBoundary extends React.Component<IProps, IState> {
    public static defaultProps = {
        reportErrorApi: resolve(DI.BackendApi),
    };

    public static getDerivedStateFromError(error: Error) {
        return {
            healthy: false,
        };
    }

    public state = {
        healthy: true,
    };

    public componentDidCatch(error: Error, errorInfo: object) {
        const {
            reportErrorApi,
        } = this.props;

        reportErrorApi.error(
            error.message,
            window.location.href,
            `${error.stack}\n\n${JSON.stringify(errorInfo, undefined, 2)}`,
            ErrorCategory.Frontend,
        );
    }

    public render() {
        const {
            healthy,
        } = this.state;

        if (! healthy) {
            return <StaticAlert type="danger">
                <strong>Something went wrong!</strong>{' '}
                Your browser raised an error while trying to create one of our components.
                We have recorded the error. Sorry about the inconvenience!
            </StaticAlert>;
        }

        return this.props.children;
    }
}
