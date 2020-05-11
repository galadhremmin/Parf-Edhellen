import { expect } from 'chai';
import {
    mount,
    ReactWrapper,
} from 'enzyme';
import React from 'react';

import Ad from '.';

import '@root/utilities/Enzyme';
import { GlobalAdsConfigurationName } from '@root/config';

describe('apps/ad', () => {
    it('does not render when there is no ads available', () => {
        const wrapper = mount(<Ad ad="frontpage" />);
        expect(wrapper.text()).to.be.empty;
        expect(wrapper.getDOMNode()).to.be.null;
    });

    it('does render when there is an ad available', () => {
        const props = {
            title: 'this is a test',
            className: 'ed-ads',
        };

        (window as any)[GlobalAdsConfigurationName] = {
            frontpage: props,
        };

        const wrapper = mount(<Ad ad="frontpage" />);
        const ad = wrapper.find('ins');
        expect(ad.length).to.equal(1);
        expect(ad.get(0).props).to.deep.equal(props);
    });
});
