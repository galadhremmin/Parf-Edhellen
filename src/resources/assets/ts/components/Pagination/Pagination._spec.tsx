import { render, screen } from '@testing-library/react';
import { expect } from 'chai';
import React from 'react';

import Pagination from './Pagination';

describe('components/Pagination', () => {
    it('is hidden by default', () => {
        const { container } = render(<Pagination />);
        expect(container.children.length).to.equal(0);
    });

    it('displays two pages', async () => {
        const pages = [1, 2];

        render(<Pagination currentPage={1} noOfPages={pages.length} pages={pages} />);

        const navigation = await screen.findAllByRole('navigation');
        expect(navigation.length).to.equal(1);
        expect(navigation[0].querySelectorAll('ul.pagination').length).to.equal(1);
        expect(navigation[0].querySelectorAll('ul.pagination > li').length).to.equal(pages.length + 1 /* next button */);
    });

    it('displays three pages', async () => {
        const pages = [1, 2, 3];

        render(<Pagination currentPage={pages.length - 1} noOfPages={pages.length} pages={pages} />);

        const navigation = await screen.findByRole('navigation');
        expect(navigation.querySelectorAll('ul.pagination > li').length).to.equal(pages.length + 2 /* next and previous buttons */);
    });

    it('displays previous but not next', async () => {
        const pages = [1, 2, 3];

        render(<Pagination currentPage={pages.length} noOfPages={pages.length} pages={pages} />);

        const navigation = await screen.findByRole('navigation');
        expect(navigation.querySelectorAll('ul.pagination > li').length).to.equal(pages.length + 1 /* previous buttons */);
    });
});
