import { DateTime } from 'luxon';
import { IProps } from './DateLabel._types';

export function DateLabel(props: IProps) {
    const {
        dateTime,
        ignoreTag,
    } = props;

    const dateTimeISOString = typeof dateTime === 'string'
        ? dateTime : dateTime?.toISOString();
    const date = typeof dateTimeISOString === 'string'
        ? DateTime.fromISO(dateTimeISOString)
        : DateTime.invalid(`${dateTime} is null or undefined.`);
    const absoluteDfateString = date.toLocaleString(DateTime.DATETIME_FULL);
    const relativeDateString = date.toRelative();

    if (! date.isValid) {
        return <span>{`Unknown date (${dateTime})`}</span>;
    }

    if (ignoreTag) {
        return <>{relativeDateString}</>;
    } 
    
    return <time className="react" dateTime={dateTimeISOString} title={absoluteDfateString}>{relativeDateString}</time>;
}

export default DateLabel;
