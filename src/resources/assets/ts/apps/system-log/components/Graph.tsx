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

const ChartColors = ['#00818a', '#404b69', '#283149', '#6c5b7c', '#c06c84', '#f67280', '#f8b595'];

function ErrorsByWeekBarGraph(props: any) {
    const {
        categories,
        data,
    } = props;

    return <ResponsiveContainer width="100%" aspect={4 / 1.5}>
        <BarChart width={730} height={250} data={data}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="week" />
            <YAxis />
            <Tooltip />
            <Legend />
            {categories.map((category: string, i: number) => <Bar
                key={category}
                dataKey={category}
                fill={ChartColors[i % ChartColors.length]}
                stackId="yearWeek"
            />)}
        </BarChart>
    </ResponsiveContainer>;
}

export default ErrorsByWeekBarGraph;
