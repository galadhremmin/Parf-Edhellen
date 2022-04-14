import { DateTime } from 'luxon';
import React from 'react';

import { IProps } from './DateLabel._types';

export function DateLabel(props: IProps) {
    const {
        dateTime,
        ignoreTag,
    } = props;

    const dateTimeISOString = typeof dateTime === 'string'
        ? dateTime : dateTime.toISOString();
    const date = DateTime.fromISO(dateTimeISOString);
    const dateString = date.toFormat('fff');

    if (ignoreTag) {
        return <>{dateString}</>;
    }
    else if (date.isValid) {
        return <time className="react" dateTime={dateTimeISOString}>{dateString}</time>;
    } else {
        return <span>{`Unknown date (${dateTime})`}</span>;
    }
}

export default DateLabel;
