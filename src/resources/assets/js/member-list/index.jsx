import React from 'react';
import ReactDOM from 'react-dom';
import {
    Bar,
    ComposedChart, 
    Line, 
    XAxis, 
    YAxis, 
    Legend,
    CartesianGrid, 
    Tooltip,
    ResponsiveContainer
} from 'recharts';

const DatasetXAxis = 'date';
const DatasetYAxis = 'number_of_items';
const ReservedDatasetProperties = [DatasetXAxis, DatasetYAxis];

const GrowthChart = props => <ResponsiveContainer width="100%" height="20%" minHeight={140}>
    <ComposedChart data={props.data}>
        <CartesianGrid strokeDasharray="3 3"/>
        <XAxis dataKey={DatasetXAxis}/>
        <YAxis/>
        <Tooltip/>
        <Line connectNulls={true} dot={false} type="monotone" dataKey={DatasetYAxis} name="Total" stroke="#8884d8" />
        {props.accounts.map(account => <Bar key={account} dataKey={account} fill="#444444" />)}
    </ComposedChart>
</ResponsiveContainer>;

const loadChart = (container, data, accounts, prerequisitePromise) => {
    if (prerequisitePromise) {
        return prerequisitePromise.then(loadChart.bind(window, container, data, accounts));
    }

    ReactDOM.render(
        <GrowthChart data={data} accounts={accounts} />,
        container
    );

    return new Promise(resolve => {
        window.setTimeout(() => resolve(), 700);
    });
};

const load = () => {
    const chartContainers = document.getElementsByClassName('ed-discuss-growth-chart');
    let promise = null;
    for (var container of chartContainers) {
        const data = JSON.parse(container.dataset.data);
        const accounts = data.reduce(function (carry, item) {
            Object.keys(item)
                .filter(v => ReservedDatasetProperties.indexOf(v) === -1)
                .forEach(account => {
                    if (carry.indexOf(account) === -1) {
                        carry.push(account);
                    }
                });

            return carry;
        }, []);
        accounts.sort();
        promise = loadChart(container, data, accounts, promise);
    }
};

load();
