import { render, screen } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';
import React from 'react';
import PaginationDetails from './PaginationDetails';

describe('apps/discuss/components/PaginationDetails', () => {

    test('supports no replies', async () => {
        render(<PaginationDetails
            currentPage={1}
            numberOfPages={1}
            numberOfPosts={1}
            numberOfTotalPosts={1}
        />);

        const text = screen.queryByRole('note');
        expect(text).toBeDefined();
        expect(text.textContent).toEqual('Viewing 0 of 0 replies - 0 through 0 (page 1 of 1)');
    });

    test('supports a couple of replies', async () => {
        render(<PaginationDetails
            currentPage={1}
            numberOfPages={1}
            numberOfPosts={8}
            numberOfTotalPosts={8}
        />);

        const text = screen.queryByRole('note');
        expect(text).toBeDefined();
        expect(text.textContent).toEqual('Viewing 7 of 7 replies - 1 through 7 (page 1 of 1)');
    });

    test('supports a multiple pages of replies', async () => {
        const { rerender  } = render(<PaginationDetails
            currentPage={1}
            numberOfPages={100}
            numberOfPosts={11}
            numberOfTotalPosts={999}
        />);

        let text = screen.queryByRole('note');
        expect(text).toBeDefined();
        expect(text.textContent).toEqual('Viewing 10 of 998 replies - 1 through 10 (page 1 of 100)');

        rerender(<PaginationDetails
            currentPage={2}
            numberOfPages={100}
            numberOfPosts={11}
            numberOfTotalPosts={999}
        />);

        text = screen.queryByRole('note');
        expect(text).toBeDefined();
        expect(text.textContent).toEqual('Viewing 10 of 998 replies - 11 through 20 (page 2 of 100)');

        rerender(<PaginationDetails
            currentPage={100}
            numberOfPages={100}
            numberOfPosts={11}
            numberOfTotalPosts={999}
        />);

        text = screen.queryByRole('note');
        expect(text).toBeDefined();
        expect(text.textContent).toEqual('Viewing 10 of 998 replies - 991 through 998 (page 100 of 100)');
    });

    test('supports unfortunate zeroes', async () => {
        render(<PaginationDetails
            currentPage={0}
            numberOfPages={0}
            numberOfPosts={0}
            numberOfTotalPosts={0}
        />);

        const text = screen.queryByRole('note');
        expect(text).toBeDefined();
        expect(text.textContent).toEqual('Viewing 0 of 0 replies - 0 through 0 (page 1 of 1)');
    });

});
