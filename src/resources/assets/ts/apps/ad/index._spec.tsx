import { expect } from 'chai';
import React from 'react';
import { render, screen } from '@testing-library/react';

import { GlobalAdsConfigurationName } from '@root/config';

import Ad from '.';
import { IGlobalAdConfiguration } from './index._types';

describe('apps/ad', () => {
    it('does not render when there is no ads available', () => {
        const wrapper = render(<Ad ad="frontpage" />);
        
        const placeholders = wrapper.container.querySelectorAll('ins');
        expect(placeholders.length).to.equal(1);
        expect(placeholders[0].classList.contains('ed-no-ad')).to.be.true;
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

        const wrapper = render(<Ad ad="frontpage" />);
        
        const ads = wrapper.container.querySelectorAll('ins');
        expect(ads.length).to.equal(1);

        Object.keys(config.props).forEach((prop) => {
            expect((ads[0] as any)[prop]).to.equal(config.props[prop]);
        });
        Object.keys(config.dataset).forEach((prop) => {
            expect(ads[0].dataset[prop]).to.equal(config.dataset[prop]);
        });
    });
});
