import { formatDateTimeFull, formatRelative, fromISOToDate } from '@root/utilities/DateTime';
import type { IProps } from './DateLabel._types';

export function DateLabel(props: IProps) {
    const {
        dateTime,
        ignoreTag,
    } = props;

    const dateTimeISOString = typeof dateTime === 'string'
        ? dateTime : dateTime?.toISOString();
    const date = typeof dateTimeISOString === 'string'
        ? fromISOToDate(dateTimeISOString)
        : null;
    const absoluteDfateString = date ? formatDateTimeFull(date) : `Unknown date (${dateTime})`;
    const relativeDateString = date ? formatRelative(date) : `Unknown date (${dateTime})`;

    if (! date) {
        return <span>{`Unknown date (${dateTime})`}</span>;
    }

    if (ignoreTag) {
        return <>{relativeDateString}</>;
    } 
    
    return <time className="react" dateTime={dateTimeISOString} title={absoluteDfateString}>{relativeDateString}</time>;
}

export default DateLabel;
