import {
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import setupContainer from '@root/di/config';
import { render } from '@testing-library/react';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import { SearchResults } from './SearchResults';

describe('apps/book-browser/containers/SearchResults', () => {
    const groups = [ 'Unit test' ];
    const searchResults: ISearchResult[][] = [
        [
            {
                id: 1,
                normalizedWord: 'word 1',
                originalWord: null,
                word: 'word 1',
            },
            {
                id: 2,
                normalizedWord: 'word 2',
                originalWord: null,
                word: 'word 2',
            },
            {
                id: 3,
                normalizedWord: 'word 3',
                originalWord: null,
                word: 'word 3',
            },
        ],
    ];

    beforeAll(() => {
        setupContainer();
    });

    test(`was mounted with ${searchResults[0].length} search results`, () => {
        const { container } = render(<SearchResults searchGroups={groups} searchResults={searchResults} word={'word'}/>);

        const list = container.querySelector('ul');
        expect(list).toEqual(expect.anything());

        const items = list.querySelectorAll('.search-result li');
        expect(items.length).toEqual(searchResults[0].length);
    });
});
