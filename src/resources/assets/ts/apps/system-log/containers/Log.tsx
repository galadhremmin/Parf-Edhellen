import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import Panel from '@root/components/Panel';
import FailedJobsList from '../components/FailedJobsList';
import ErrorsByWeekBarGraph from '../components/Graph';
import LogList from '../components/LogList';
import { IProps } from '../index._types';

function Log(props: IProps) {
    const {
        errorsByWeek,
        errorCategories,
        failedJobsByWeek,
        failedJobsCategories,
        logApi,
    } = props;

    return <>
        <Panel title="Exception log" shadow={true}>
            {errorsByWeek && <section>
                <ErrorsByWeekBarGraph data={errorsByWeek} categories={errorCategories} />
            </section>}
            <section>
                <LogList logApi={logApi} />
            </section>
        </Panel>
        <Panel title="Failed jobs" shadow={true}>
            {failedJobsByWeek && <section>
                <ErrorsByWeekBarGraph data={failedJobsByWeek} categories={failedJobsCategories} />
            </section>}
            <section>
                <FailedJobsList logApi={logApi} />
            </section>
        </Panel>
    </>;
}

export default withPropInjection(Log, {
    logApi: DI.LogApi,
});
