import { expect } from 'chai';
import React from 'react';

import { IAccountEntity } from '../connectors/backend/IBookApi';
import ProfileLink from './ProfileLink';

/*
describe('components/ProfileLink', () => {
    const SampleAccount = {
        id: Math.floor(Math.random() * 1000),
        nickname: 'Glorfindel',
    } as Partial<IAccountEntity>;

    let wrapper: ReactWrapper;

    before(() => {
        // Throwing the `SampleAccount` as `any` to suppress the TypeScript compiler error related to
        // non-essential properties on `IAccountEntity` are optional (due to `Partial` above) on the
        // `account` attribute.
        wrapper = mount(<ProfileLink account={SampleAccount as any} />);
    });

    it('renders a clickable link', () => {
        expect(wrapper.find('a').prop('href')).to.equal(`/author/${SampleAccount.id}`);
        expect(wrapper.find('a').prop('title')).to.equal(`View ${SampleAccount.nickname}'s profile`);
        expect(wrapper.find('a').text()).to.equal(SampleAccount.nickname);
    });

    it('supports `className` attribute', () => {
        const className = 'test';
        wrapper.setProps({
            className,
        });

        expect(wrapper.find('a').prop('className')).to.equal(className);
    });

    it('gracefully handles erroneous input', () => {
        const nullWrapper = mount(<ProfileLink account={null} />);
        const badInput = [ null, undefined, 1, 'string', 1.521 ];

        for (const account of badInput) {
            nullWrapper.setProps({
                account,
            });

            expect(nullWrapper.children()).to.be.empty;
        }
    });
});
*/
