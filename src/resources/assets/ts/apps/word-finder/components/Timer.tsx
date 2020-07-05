import React, { useState, useEffect } from 'react';
import moment from 'moment';

import { fireEvent } from '@root/components/Component';
import { IProps } from './Timer._types';

function Timer(props: IProps) {
    const {
        onTick,
        startValue,
        value,
    } = props;

    useEffect(() => {
        const timer = window.setInterval(() => {
            const now  = moment();
            fireEvent('Timer', onTick, now.unix());
        }, 1000);

        return () => {
            window.clearInterval(timer);
        }
    }, []);

    const duration = moment.unix(value).diff(
        moment.unix(startValue), 'seconds',
    );
    return <span>
        {duration}
    </span>;
}

Timer.defaultProps = {
    startValue: moment().unix(),
    value: moment().unix(),
} as IProps;

export default Timer;
