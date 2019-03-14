import React, { useRef, useCallback } from 'react';
import { IProps } from './Chart._types';

const DatasetXAxis = 'date';
const DatasetYAxis = 'numberOfItems';
const ReservedDatasetProperties = [DatasetXAxis, DatasetYAxis];

function Chart(props: IProps) {
    const accounts = props.data.reduce(function (carry, item) {
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

    return <span>chart</span>;
}

export default Chart;
