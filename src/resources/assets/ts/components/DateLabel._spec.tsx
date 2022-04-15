import { expect } from 'chai';
import { mount } from 'enzyme';
import { DateTime } from 'luxon';
import React from 'react';

import DateLabel from './DateLabel';

import '../utilities/Enzyme';

describe('components/DateLabel', () => {
    it('formats Date appropriately', () => {
        const dateTime = DateTime.now().minus({ month: 1 }).toJSDate();
        const wrapper = mount(<DateLabel dateTime={dateTime} />);

        expect(wrapper.find('time').prop('dateTime')).to.equal(dateTime.toISOString());
        expect(wrapper.find('time').prop('title')).to.equal(DateTime.fromJSDate(dateTime).toLocaleString(DateTime.DATETIME_FULL));
        expect(wrapper.find('time').text()).to.equal(DateTime.fromJSDate(dateTime).toRelative());
    });

    it('formats ISO string dates appropriately', () => {
        const dateTime = DateTime.now().minus({ year: 1 }).toISOTime();
        const wrapper = mount(<DateLabel dateTime={dateTime} />);

        expect(wrapper.find('time').prop('dateTime')).to.equal(dateTime);
        expect(wrapper.find('time').prop('title')).to.equal(DateTime.fromISO(dateTime).toLocaleString(DateTime.DATETIME_FULL));
        expect(wrapper.find('time').text()).to.equal(DateTime.fromISO(dateTime).toRelative());
    });

    it('handles failures', () => {
        const dateTime = 'Lá hanyan lúme!';
        const wrapper = mount(<DateLabel dateTime={dateTime} />);

        expect(wrapper.find('time')).to.be.empty;
        expect(wrapper.find('span').prop('dateTime')).to.be.undefined;
        expect(wrapper.find('span').text()).to.equal(`Unknown date (${dateTime})`);
    });
});
