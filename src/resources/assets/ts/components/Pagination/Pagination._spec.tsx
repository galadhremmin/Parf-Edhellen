import { render, screen } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';
import React from 'react';

import Pagination from './Pagination';

describe('components/Pagination', () => {
    test('is hidden by default', () => {
        const { container } = render(<Pagination />);
        expect(container.children.length).toEqual(0);
    });

    test('displays two pages', async () => {
        const pages = [1, 2];

        render(<Pagination currentPage={1} noOfPages={pages.length} pages={pages} />);

        const navigation = await screen.findAllByRole('navigation');
        expect(navigation.length).toEqual(1);
        expect(navigation[0].querySelectorAll('ul.pagination').length).toEqual(1);
        expect(navigation[0].querySelectorAll('ul.pagination > li').length).toEqual(pages.length + 1 /* next button */);
    });

    test('displays three pages', async () => {
        const pages = [1, 2, 3];

        render(<Pagination currentPage={pages.length - 1} noOfPages={pages.length} pages={pages} />);

        const navigation = await screen.findByRole('navigation');
        expect(navigation.querySelectorAll('ul.pagination > li').length).toEqual(pages.length + 2 /* next and previous buttons */);
    });

    test('displays previous but not next', async () => {
        const pages = [1, 2, 3];

        render(<Pagination currentPage={pages.length} noOfPages={pages.length} pages={pages} />);

        const navigation = await screen.findByRole('navigation');
        expect(navigation.querySelectorAll('ul.pagination > li').length).toEqual(pages.length + 1 /* previous buttons */);
    });
});
