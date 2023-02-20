import {
    Bar,
    CartesianGrid,
    ComposedChart,
    Line,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import { IProps } from './GrowthChart._types';

export const DatasetXAxis = 'date';
export const DatasetYAxis = 'numberOfItems';
export const ReservedDatasetProperties = [DatasetXAxis, DatasetYAxis];

const GrowthChart = (props: IProps) => <ResponsiveContainer width="100%" height="20%" minHeight={140}>
    <ComposedChart data={props.data}>
        <CartesianGrid strokeDasharray="3 3"/>
        <XAxis dataKey={DatasetXAxis}/>
        <YAxis/>
        <Tooltip/>
        <Line connectNulls={true}
              dot={false}
              isAnimationActive={false}
              type="monotone"
              dataKey={DatasetYAxis}
              name="Total"
              stroke="#8884d8"
        />
        {props.accounts.map((account: string) => <Bar key={account}
            dataKey={account}
            isAnimationActive={false}
            fill="#444444"
        />)}
    </ComposedChart>
</ResponsiveContainer>;

export default GrowthChart;
