import { dateNowInMilliseconds } from '@root/utilities/DateTime';
import { useEffect } from 'react';

import { fireEvent } from '@root/components/Component';
import type { IProps } from './Timer._types';

function Timer(props: IProps) {
    const {
        onTick,
        tick,
        startValue = dateNowInMilliseconds(),
        value = dateNowInMilliseconds(),
    } = props;

    useEffect(() => {
        if (! tick) {
            return;
        }

        const timer = window.setInterval(() => {
            const now  = dateNowInMilliseconds();
            void fireEvent('Timer', onTick, now);
        }, 1000);

        return () => {
            window.clearInterval(timer);
        }
    }, [ tick ]);

    const durationSeconds = Math.max(0, Math.round((value - startValue) / 1000));
    return <span>
        {durationSeconds}
    </span>;
}

export default Timer;
