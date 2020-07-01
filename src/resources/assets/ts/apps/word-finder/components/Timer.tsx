import React, { useState, useEffect } from 'react';
import moment from 'moment';

function Timer() {
    const [ startTime ] = useState(() => moment());
    const [ lastRenderTime, setElapsedTime ] = useState(startTime);

    useEffect(() => {
        // time called???
        console.log('called');
        const timer = window.setInterval(() => {
            setElapsedTime(moment());
        }, 1000);

        return () => {
            window.clearInterval(timer);
        }
    }, []);

    const duration = lastRenderTime.diff(startTime, 'seconds');
    return <span>
        {duration}
    </span>;
}

export default Timer;
