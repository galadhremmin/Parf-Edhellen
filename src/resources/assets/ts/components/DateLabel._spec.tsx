import { render, screen } from '@testing-library/react';
import { expect } from 'chai';
import { DateTime } from 'luxon';
import React from 'react';

import DateLabel from './DateLabel';

describe('components/DateLabel', () => {
    it('formats Date appropriately', () => {
        const dateTime = DateTime.now().minus({ month: 1 }).toJSDate();
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time.getAttribute('dateTime')).to.equal(dateTime.toISOString());
        expect(time.getAttribute('title')).to.equal(DateTime.fromJSDate(dateTime).toLocaleString(DateTime.DATETIME_FULL));
        expect(time.textContent).to.equal(DateTime.fromJSDate(dateTime).toRelative());
    });

    it('formats ISO string dates appropriately', () => {
        const dateTime = DateTime.now().minus({ year: 1 }).toISOTime();
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time.getAttribute('dateTime')).to.equal(dateTime);
        expect(time.getAttribute('title')).to.equal(DateTime.fromISO(dateTime).toLocaleString(DateTime.DATETIME_FULL));
        expect(time.textContent).to.equal(DateTime.fromISO(dateTime).toRelative());
    });

    it('handles failures', () => {
        const dateTime = 'Lá hanyan lúme!';
        const { container } = render(<DateLabel dateTime={dateTime} />);

        const time = container.querySelector('time');
        expect(time).to.not.exist;
        expect(container.querySelector('span').getAttribute('dateTime')).to.be.null;
        expect(container.querySelector('span').textContent).to.equal(`Unknown date (${dateTime})`);
    });
});
