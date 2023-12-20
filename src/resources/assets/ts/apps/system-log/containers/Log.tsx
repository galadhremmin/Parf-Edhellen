import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

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
        <h3>Exception log</h3>
        {errorsByWeek && <section>
            <ErrorsByWeekBarGraph data={errorsByWeek} categories={errorCategories} />
        </section>}
        <section>
            <LogList logApi={logApi} />
        </section>
        <h3>Failed jobs</h3>
        {failedJobsByWeek && <section>
            <ErrorsByWeekBarGraph data={failedJobsByWeek} categories={failedJobsCategories} />
        </section>}
        <section>
            <FailedJobsList logApi={logApi} />
        </section>
    </>;
}

export default withPropInjection(Log, {
    logApi: DI.LogApi,
});
