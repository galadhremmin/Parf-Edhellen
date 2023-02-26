import { render } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';

import { GlobalAdsConfigurationName } from '@root/config';

import Ad from '.';
import { IGlobalAdConfiguration } from './index._types';

describe('apps/ad', () => {
    test('does not render when there is no ads available', () => {
        const wrapper = render(<Ad ad="frontpage" />);
        
        const placeholders = wrapper.container.querySelectorAll('ins');
        expect(placeholders.length).toEqual(1);
        expect(placeholders[0].classList.contains('ed-no-ad')).toBeTruthy();
    });

    test('does render when there is an ad available', () => {
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
        expect(ads.length).toEqual(1);

        Object.keys(config.props).forEach((prop) => {
            expect((ads[0] as any)[prop]).toEqual(config.props[prop]);
        });
        Object.keys(config.dataset).forEach((prop) => {
            expect(ads[0].dataset[prop]).toEqual(config.dataset[prop]);
        });
    });
});
