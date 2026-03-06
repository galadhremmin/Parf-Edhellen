import {
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import type { IViewsPerDay } from '../index._types';

interface IProps {
    data: IViewsPerDay[];
}

function ViewsGraph({ data }: IProps) {
    if (!data?.length) {
        return null;
    }

    return <ResponsiveContainer width="100%" aspect={4 / 1}>
        <LineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="date" />
            <YAxis />
            <Tooltip />
            <Legend />
            <Line type="monotone" dataKey="count" name="Views" stroke="#00818a" dot={false} />
        </LineChart>
    </ResponsiveContainer>;
}

export default ViewsGraph;
