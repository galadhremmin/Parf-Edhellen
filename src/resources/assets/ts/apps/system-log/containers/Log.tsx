import { DI, resolve } from '@root/di';

import LogList from '../components/LogList';
import { IProps } from '../index._types';
import ErrorsByWeekBarGraph from '../components/Graph';
import FailedJobsList from '../components/FailedJobsList';

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

Log.defaultProps = {
    logApi: resolve(DI.UtilityApi),
} as Partial<IProps>;

export default Log;
