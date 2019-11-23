import { expect } from 'chai';
import { mount } from 'enzyme';
import moment from 'moment';
import React from 'react';

import DateLabel from './DateLabel';

import '../utilities/Enzyme';

describe('components/DateLabel', () => {
    it('formats Date appropriately', () => {
        const dateTime = new Date();
        const wrapper = mount(<DateLabel dateTime={dateTime} />);

        expect(wrapper.find('time').prop('dateTime')).to.equal(dateTime.toISOString());
        expect(wrapper.find('time').text()).to.equal(moment(dateTime).format('LLL'));
    });

    it('formats ISO string dates appropriately', () => {
        const dateTime = new Date().toISOString();
        const wrapper = mount(<DateLabel dateTime={dateTime} />);

        expect(wrapper.find('time').prop('dateTime')).to.equal(dateTime);
        expect(wrapper.find('time').text()).to.equal(moment(dateTime).format('LLL'));
    });

    it('handles failures', () => {
        const dateTime = 'Lá hanyan lúme!';
        const wrapper = mount(<DateLabel dateTime={dateTime} />);

        expect(wrapper.find('time')).to.be.empty;
        expect(wrapper.find('span').prop('dateTime')).to.be.undefined;
        expect(wrapper.find('span').text()).to.equal(`Unknown date (${dateTime})`);
    });
});
