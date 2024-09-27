import React from 'react';

import StaticAlert from '@root/components/StaticAlert';
import { ErrorCategory } from '@root/connectors/IReportErrorApi';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import { IProps, IState } from './ErrorBoundary._types';

export class ErrorBoundary extends React.Component<IProps, IState> {
    private static excludeErrorMessages: RegExp[] = [
        /Loading chunk [0-9]+ failed\./,
        /Loading CSS chunk [0-9]+ failed\./,
        /Request aborted/,
    ];

    public state = {
        healthy: true,
    };

    public static getDerivedStateFromError() {
        return {
            healthy: false,
        };
    }

    public async componentDidCatch(error: Error, errorInfo: object) {
        const {
            reportErrorApi,
        } = this.props;

        // async chunk loading errors are deliberately excluded
        if (ErrorBoundary.excludeErrorMessages.some((reg) => reg.test(error.message))) {
            return;
        }

        await reportErrorApi.error(
            error.message,
            window.location.href,
            `${error.stack}\n\n${JSON.stringify(errorInfo, undefined, 2)}`,
            ErrorCategory.Frontend,
        );
    }

    public componentDidMount() {
        const events = this.props.globalEvents;
        if (events) {
            events.errorLogger = this._onError;
        }
    }

    public componentWillUnmount() {
        this.props.globalEvents?.disconnect();
    }

    private _onError = async (evt: CustomEvent<Error>) => {
        await this.componentDidCatch(evt.detail, {});
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

export default withPropInjection(ErrorBoundary, {
    reportErrorApi: DI.BackendApi,
    globalEvents: DI.GlobalEvents,
});
