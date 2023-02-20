import { DateTime } from 'luxon';
import { IProps } from './DateLabel._types';

export function DateLabel(props: IProps) {
    const {
        dateTime,
        ignoreTag,
    } = props;

    const dateTimeISOString = typeof dateTime === 'string'
        ? dateTime : dateTime.toISOString();
    const date = DateTime.fromISO(dateTimeISOString);
    const absoluteDfateString = date.toLocaleString(DateTime.DATETIME_FULL);
    const relativeDateString = date.toRelative();

    if (ignoreTag) {
        return <>{relativeDateString}</>;
    }
    else if (date.isValid) {
        return <time className="react" dateTime={dateTimeISOString} title={absoluteDfateString}>{relativeDateString}</time>;
    } else {
        return <span>{`Unknown date (${dateTime})`}</span>;
    }
}

export default DateLabel;
