import { render } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';
import { formatDateTimeFull, formatRelative } from '@root/utilities/DateTime';
import DateLabel from './DateLabel';

describe('components/DateLabel', () => {
    test('formats Date appropriately', () => {
        const dateTime = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time.getAttribute('dateTime')).toEqual(dateTime.toISOString());
        expect(time.getAttribute('title')).toEqual(formatDateTimeFull(dateTime));
        expect(time.textContent).toEqual(formatRelative(dateTime));
    });

    test('formats ISO string dates appropriately', () => {
        const dateTime = new Date(Date.now() - 365 * 24 * 60 * 60 * 1000).toISOString();
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time.getAttribute('dateTime')).toEqual(dateTime);
        expect(time.getAttribute('title')).toEqual(formatDateTimeFull(dateTime));
        // Relative text can vary slightly with time; just assert it contains a year difference notion
        expect(time.textContent).toEqual(formatRelative(dateTime));
    });

    test('handles failures', () => {
        const dateTime = 'Lá hanyan lúme!';
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time).not.toEqual(expect.anything());
        expect(container.querySelector('span').getAttribute('dateTime')).toBeNull();
        expect(container.querySelector('span').textContent).toEqual(`Unknown date (${dateTime})`);
    });
});
