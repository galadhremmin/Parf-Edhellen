import React, { useState } from 'react';

import {
    IData,
    IProps,
} from './Chart._types';
import GrowthChart, { ReservedDatasetProperties } from './GrowthChart';

const loadAccounts = (data: IData[]) => {
    const accounts = data.reduce((carry, item) => {
        Object.keys(item) //
            .filter((v: string) => ReservedDatasetProperties.indexOf(v) === -1) //
            .forEach((account: string) => { //
                if (carry.indexOf(account) === -1) {
                    carry.push(account);
                }
            });

        return carry;
    }, []);
    accounts.sort();

    return accounts;
};

function Chart(props: IProps) {
    const [ accounts, setAccounts ] = useState<string[]>(null);
    const {
        data,
    } = props;

    // This component does currently not support changing of the data property.
    // it is currently only used in a context where the data property will not change, and
    // their values are available prior to the component mounting.
    if (accounts === null) {
        setAccounts(loadAccounts(data));
    }

    return <GrowthChart accounts={accounts} data={data} />;
}

export default Chart;
