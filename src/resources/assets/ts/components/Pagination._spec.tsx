import { expect } from 'chai';
import {
    mount,
    ReactWrapper,
} from 'enzyme';
import React from 'react';

import Pagination from './Pagination';

import '../utilities/Enzyme';

describe('components/Pagination', () => {
    let wrapper: ReactWrapper;

    beforeEach(() => {
        wrapper = mount(<Pagination />);
    });

    it('is hidden by default', () => {
        expect(wrapper.children().length).to.equal(0);
    });

    it('displays two pages', () => {
        const pages = [1, 2];
        wrapper.setProps({
            currentPage: 1,
            noOfPages: pages.length,
            pages,
        });

        expect(wrapper.find('nav').length).to.equal(1);
        expect(wrapper.find('ul.pagination').length).to.equal(1);
        expect(wrapper.find('ul.pagination > li').length).to.equal(pages.length + 1 /* next button */);
    });

    it('displays three pages', () => {
        const pages = [1, 2, 3];
        wrapper.setProps({
            currentPage: pages.length - 1,
            noOfPages: pages.length,
            pages,
        });

        expect(wrapper.find('ul.pagination > li').length).to.equal(pages.length + 2 /* next and previous buttons */);
    });

    it('displays previous but not next', () => {
        const pages = [1, 2, 3];
        wrapper.setProps({
            currentPage: pages.length,
            noOfPages: pages.length,
            pages,
        });

        expect(wrapper.find('ul.pagination > li').length).to.equal(pages.length + 1 /* previous buttons */);
    });
});
