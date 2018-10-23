import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';

import '../../../utilities/Enzyme';

import SearchQueryInput from './SearchQueryInput';

describe('apps/book-browser/components/SearchQueryInput', () => {
    let wrapper: ReactWrapper;

    before(() => {
        wrapper = mount(<SearchQueryInput name="unit-test" onChange={null} />);
    });

    it('should mount', () => {
        expect(wrapper.find('input')).to.not.equal(undefined);
    });

});
