import moment from 'moment';
import React from 'react';

import { IProps } from './DateLabel._types';

export function DateLabel(props: IProps) {
    return <time dateTime={props.dateTime}>
        {moment(props.dateTime).format('LLL')}
    </time>;
}

export default DateLabel;
