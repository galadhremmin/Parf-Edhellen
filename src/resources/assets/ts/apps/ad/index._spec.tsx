import { expect } from 'chai';
import {
    mount,
    ReactWrapper,
} from 'enzyme';
import React from 'react';

import { GlobalAdsConfigurationName } from '@root/config';
import '@root/utilities/Enzyme';

import Ad from '.';
import { IGlobalAdConfiguration } from './index._types';

describe('apps/ad', () => {
    it('does not render when there is no ads available', () => {
        const wrapper = mount(<Ad ad="frontpage" />);
        expect(wrapper.text()).to.be.empty;
        expect(wrapper.getDOMNode()).to.be.null;
    });

    it('does render when there is an ad available', () => {
        const config: IGlobalAdConfiguration = {
            props: {
                title: 'this is a test',
                className: 'ed-ads',
            },
            dataset: {
                helloWorld: 'mae govannen!',
            },
        };

        (window as any)[GlobalAdsConfigurationName] = {
            frontpage: config,
        };

        const wrapper = mount(<Ad ad="frontpage" />);
        const ad = wrapper.find('ins');
        expect(ad.length).to.equal(1);
        expect(ad.get(0).props).to.deep.equal({
            ...config.props,
            'data-hello-world': 'mae govannen!',
        });
    });
});
