import {
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis
} from 'recharts';

import type { IProps } from './Graph._types';

const ChartColors = ['#00818a', '#404b69', '#283149', '#6c5b7c', '#c06c84', '#f67280', '#f8b595'];

function Graph(props: IProps) {
    const {
        categories,
        data,
    } = props;

    if (! Array.isArray(categories)) {
        return null;
    }

    return <ResponsiveContainer width="100%" aspect={4 / 1.5}>
        <LineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="week" />
            <YAxis scale="auto" />
            <Tooltip />
            <Legend />
            {categories.map((category: string, i: number) => <Line
                key={category}
                dataKey={category}
                stroke={ChartColors[i % ChartColors.length]}
                strokeWidth="2"
                connectNulls={true}
            />)}
        </LineChart>
    </ResponsiveContainer>;
}

export default Graph;
