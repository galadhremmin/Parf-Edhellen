import moment from 'moment';
import React from 'react';

import { IProps } from './DateLabel._types';

export function DateLabel(props: IProps) {
    const {
        dateTime,
    } = props;

    const dateTimeISOString = typeof dateTime === 'string'
        ? dateTime : dateTime.toISOString();
    const momentDate = moment(dateTime, moment.ISO_8601, true);

    if (momentDate.isValid()) {
        return <time dateTime={dateTimeISOString}>{momentDate.format('LLL')}</time>;
    } else {
        return <span>{`Unknown date (${dateTime})`}</span>;
    }
}

export default DateLabel;
