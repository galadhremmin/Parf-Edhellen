import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';
import sinon from 'sinon';

import '../../../utilities/Enzyme';

import SearchQueryInput from './SearchQueryInput';

describe('apps/book-browser/components/SearchQueryInput', () => {
    let wrapper: ReactWrapper;

    before(() => {
        const noop = (ev: any) => { expect(ev).to.to.exist; };
        wrapper = mount(<SearchQueryInput name="unit-test" onChange={noop} />);
    });

    it('is mounted', () => {
        expect(wrapper.exists()).to.be.true;
    });

    it('will propagate props', () => {
        const expectedProps: any = {
            name: 'unit-test-name',
            value: 'a value',
        };

        wrapper.setProps(expectedProps);

        const input = wrapper.find('input');
        for (const prop of Object.keys(expectedProps)) {
            expect(input.prop(prop)).to.equal(expectedProps[prop]);
        }
    });

    it('will notify on change', () => {
        const expectedValue = 'a value';
        const expectedChangeArguments = {
            name: wrapper.prop('name'),
            value: expectedValue,
        };
        const changeStub = sinon.stub();
        const input = wrapper.find('input');

        wrapper.setProps({
            onChange: changeStub,
            value: expectedValue,
        });

        input.simulate('change');
        expect(changeStub.callCount).to.equal(1);
        expect(changeStub.firstCall.args[0]).to.deep.equal(expectedChangeArguments);
    });
});
