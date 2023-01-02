import { render, screen } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';
import React from 'react';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import SearchResult from './SearchResult';

describe('apps/book-browser/components/SearchResultsContainer', () => {
    const searchResult: ISearchResult = {
        id: 1,
        normalizedWord: 'normalized word',
        originalWord: null,
        word: 'word',
    };

    test('displays a word', async () => {
        render(<SearchResult searchResult={searchResult} />);

        const link = await screen.findByRole('link');
        expect(link).toEqual(expect.anything());
        expect(link.getAttribute('href')).toEqual('#');

        const word = link.querySelector('.word');
        expect(word).toEqual(expect.anything());
        expect(word.textContent).toEqual(searchResult.word);
    });

    test('displays original word', () => {

        const newSearchResult = { ...searchResult, originalWord: 'original word' };
        const { container } = render(<SearchResult searchResult={newSearchResult} />);

        const word = container.querySelector('.word');
        expect(word).toEqual(expect.anything());
        expect(word.textContent).toEqual(newSearchResult.originalWord);

        const originalWord = container.querySelector('.development');
        expect(originalWord).toEqual(expect.anything());
        expect(originalWord.textContent).toEqual(newSearchResult.word);
    });

    test('selects the word', () => {
        const { container } = render(<SearchResult searchResult={searchResult} selected={true} />);

        const link = container.querySelector('a.selected');
        expect(link).toEqual(expect.anything());
        expect(link.querySelector('.word').textContent).toEqual(searchResult.word);
    });
});
