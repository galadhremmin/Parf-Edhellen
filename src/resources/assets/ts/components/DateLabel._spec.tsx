import { render, screen } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';
import { DateTime } from 'luxon';
import React from 'react';

import DateLabel from './DateLabel';

describe('components/DateLabel', () => {
    test('formats Date appropriately', () => {
        const dateTime = DateTime.now().minus({ month: 1 }).toJSDate();
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time.getAttribute('dateTime')).toEqual(dateTime.toISOString());
        expect(time.getAttribute('title')).toEqual(DateTime.fromJSDate(dateTime).toLocaleString(DateTime.DATETIME_FULL));
        expect(time.textContent).toEqual(DateTime.fromJSDate(dateTime).toRelative());
    });

    test('formats ISO string dates appropriately', () => {
        const dateTime = DateTime.now().minus({ year: 1 }).toISOTime();
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time.getAttribute('dateTime')).toEqual(dateTime);
        expect(time.getAttribute('title')).toEqual(DateTime.fromISO(dateTime).toLocaleString(DateTime.DATETIME_FULL));
        expect(time.textContent).toEqual(DateTime.fromISO(dateTime).toRelative());
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
