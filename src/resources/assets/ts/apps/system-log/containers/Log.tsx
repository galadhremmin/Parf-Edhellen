import React, {
    useCallback,
    useEffect,
    useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import { IErrorEntity } from '@root/connectors/backend/ILogApi';
import { DI, resolve } from '@root/di';

import LogList from '../components/LogList';
import { IProps } from '../index._types';
import ErrorsByWeekBarGraph from '../components/Graph';


function Log(props: IProps) {
    const {
        errorsByWeek,
        errorCategories,
        failedJobsByWeek,
        failedJobsCategories,
        logApi,
    } = props;

    const [ loadedPage, setLoadedPage ] = useState<number>(0);
    const [ currentPage, setCurrentPage ] = useState<number>(1);
    const [ noOfPages, setNoOfPages ] = useState<number>(null);
    const [ logs, setLogs ] = useState<IErrorEntity[]>(null);

    const _loadLogs = useCallback(async (page: number) => {
        if (currentPage === loadedPage) {
            return;
        }

        const response = await logApi.getErrors(page);

        setLoadedPage(response.currentPage);
        setNoOfPages(response.lastPage);
        setLogs(response.data);
    }, [ logApi, currentPage, loadedPage ]);

    const _onClick = useCallback((ev: IComponentEvent<number>) => {
        setCurrentPage(ev.value);
    }, []);

    useEffect(() => {
        void _loadLogs(currentPage);
    }, [ currentPage ]);

    return <>
        <h3>Exception log</h3>
        {errorsByWeek && <section>
            <ErrorsByWeekBarGraph data={errorsByWeek} categories={errorCategories} />
        </section>}
        <section>
            <LogList currentPage={currentPage}
                     logs={logs}
                     onClick={_onClick}
                     noOfPages={noOfPages}
            />
        </section>
        <h3>Failed jobs</h3>
        {failedJobsByWeek && <section>
            <ErrorsByWeekBarGraph data={failedJobsByWeek} categories={failedJobsCategories} />
        </section>}
    </>;
}

Log.defaultProps = {
    logApi: resolve(DI.UtilityApi),
} as Partial<IProps>;

export default Log;
