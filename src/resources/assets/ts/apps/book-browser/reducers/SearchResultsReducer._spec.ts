import { expect } from 'chai';

import { Actions } from '../actions';
import SearchResultsReducer from './SearchResultsReducer';
import { ISearchResult } from './SearchResultsReducer._types';

describe('apps/book-browser/reducers/SearchResultsReducer', () => {
    it ('filters out redundant results', () => {
        const input: ISearchResult[] = [
            {
                id: 1,
                normalizedWord: 'elf',
                originalWord: null,
                word: 'elf',
            },
            {
                id: 2,
                normalizedWord: 'elf',
                originalWord: 'elf',
                word: 'elf',
            },
            {
                id: 3,
                normalizedWord: 'elfin',
                originalWord: 'elf',
                word: 'elfin',
            },
        ];
        const expected = [
            {
                id: 1,
                normalizedWord: 'elf',
                originalWord: null,
                word: 'elf',
            },
            {
                id: 3,
                normalizedWord: 'elfin',
                originalWord: 'elf',
                word: 'elfin',
            },
        ];

        const actual = SearchResultsReducer([], {
            searchResults: input,
            type: Actions.ReceiveSearchResults,
        });

        expect(actual).to.deep.equal(expected);
    });
});
