import { DateTime } from 'luxon';
import React from 'react';

import { IProps } from './DateLabel._types';

export function DateLabel(props: IProps) {
    const {
        dateTime,
    } = props;

    const dateTimeISOString = typeof dateTime === 'string'
        ? dateTime : dateTime.toISOString();
    const date = DateTime.fromISO(dateTimeISOString);

    if (date.isValid) {
        return <time dateTime={dateTimeISOString}>{date.toFormat('LLL')}</time>;
    } else {
        return <span>{`Unknown date (${dateTime})`}</span>;
    }
}

export default DateLabel;
