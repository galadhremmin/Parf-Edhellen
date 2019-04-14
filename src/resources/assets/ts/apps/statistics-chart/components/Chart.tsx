import React, {
    useCallback,
    useState,
} from 'react';
import { Waypoint } from 'react-waypoint';

import {
    IData,
    IProps,
} from './Chart._types';
import GrowthChart, { ReservedDatasetProperties } from './GrowthChart';
import Spinner from '@root/components/Spinner';

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
    const [ isVisible, setIsVisible ] = useState<boolean>(false);
    const {
        data,
    } = props;

    const _onWaypointEnter = useCallback(() => {
        setIsVisible(true);
    }, [ setIsVisible ]);

    // This component does currently not support changing of the data property.
    // it is currently only used in a context where the data property will not change, and
    // their values are available prior to the component mounting.
    if (accounts === null) {
        setAccounts(loadAccounts(data));
    }

    return <Waypoint onEnter={_onWaypointEnter}>
        <div>
            {isVisible
                ? <GrowthChart accounts={accounts} data={data} />
                : <Spinner />
            }
        </div>
    </Waypoint>
}

export default Chart;
