import React, {
    useCallback,
    useEffect,
    useState,
} from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import { IComponentEvent } from '@root/components/Component._types';
import { IErrorEntity } from '@root/connectors/backend/ILogApi';
import UtilityApiConnector from '@root/connectors/backend/UtilityApiConnector';
import { ErrorCategory } from '@root/connectors/IReportErrorApi';
import SharedReference from '@root/utilities/SharedReference';

import LogList from '../components/LogList';
import { IProps } from '../index._types';

const ChartColors = ['#00818a', '#404b69', '#283149', '#6c5b7c', '#c06c84', '#f67280', '#f8b595'];
const ErrorCategories = [ ErrorCategory.Backend, ErrorCategory.Frontend ];

function Log(props: IProps) {
    const {
        errorsByWeek,
        logApi,
    } = props;

    const [ loadedPage, setLoadedPage ] = useState<number>(0);
    const [ currentPage, setCurrentPage ] = useState<number>(
        () => {
            const hash = window.location.hash;
            return /#?\d+/.test(hash) ? parseInt(hash.substr(1), 10) : 1;
        },
    );
    const [ noOfPages, setNoOfPages ] = useState<number>(null);
    const [ logs, setLogs ] = useState<IErrorEntity[]>(null);

    const _loadLogs = useCallback(async (page: number) => {
        if (currentPage === loadedPage) {
            return;
        }

        const response = await logApi.getErrors(page);

        setLogs(response.data);
        setCurrentPage(response.currentPage);
        setLoadedPage(response.currentPage);
        setNoOfPages(response.lastPage);

        window.location.hash = `#${response.currentPage}`;
    }, [ logApi, currentPage, loadedPage ]);

    const _onClick = useCallback((ev: IComponentEvent<number>) => {
        _loadLogs(ev.value);
    }, []);

    useEffect(() => {
        _loadLogs(currentPage);
    }, [ currentPage ]);

    return <>
        {errorsByWeek && <section>
            <ResponsiveContainer width="100%" aspect={4 / 1.5}>
                <BarChart width={730} height={250} data={errorsByWeek}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="week" />
                    <YAxis />
                    <Tooltip />
                    <Legend />
                    {Object.values(ErrorCategories).map((category: ErrorCategory, i: number) => <Bar key={category}
                        dataKey={category} fill={ChartColors[i % ChartColors.length]} stackId="week" />)}
                </BarChart>
            </ResponsiveContainer>
        </section>}
        <section>
            <LogList currentPage={currentPage}
                     logs={logs}
                     onClick={_onClick}
                     noOfPages={noOfPages}
            />
        </section>
    </>;
}

Log.defaultProps = {
    logApi: SharedReference.getInstance(UtilityApiConnector),
} as Partial<IProps>;

export default Log;
