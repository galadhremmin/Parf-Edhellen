import React from 'react';
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

import { ErrorCategory } from '@root/connectors/IReportErrorApi';
import { IProps } from '../index._types';

const ChartColors = ['#00818a', '#404b69', '#283149', '#6c5b7c', '#c06c84', '#f67280', '#f8b595'];
const ErrorCategories = [ ErrorCategory.Backend, ErrorCategory.Frontend ];

function Log(props: IProps) {
    const {
        errorsByWeek,
    } = props;

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
    </>;
}

export default Log;
