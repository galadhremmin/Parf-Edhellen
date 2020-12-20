import React, { useState, useEffect } from 'react';
import { DateTime } from 'luxon';

import { fireEvent } from '@root/components/Component';
import { IProps } from './Timer._types';

function Timer(props: IProps) {
    const {
        onTick,
        startValue,
        tick,
        value,
    } = props;

    useEffect(() => {
        if (! tick) {
            return;
        }

        const timer = window.setInterval(() => {
            const now  = DateTime.local();
            fireEvent('Timer', onTick, now.toMillis());
        }, 1000);

        return () => {
            window.clearInterval(timer);
        }
    }, [ tick ]);

    const duration = DateTime.fromMillis(value).diff(
        DateTime.fromMillis(startValue), 'seconds',
    );
    return <span>
        {duration}
    </span>;
}

Timer.defaultProps = {
    startValue: DateTime.local().toMillis(),
    value: DateTime.local().toMillis(),
} as IProps;

export default Timer;
