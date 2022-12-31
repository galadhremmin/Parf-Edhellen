import { render, screen } from '@testing-library/react';
import { expect } from 'chai';
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

    it('displays a word', async () => {
        render(<SearchResult searchResult={searchResult} />);

        const link = await screen.findByRole('link');
        expect(link).to.exist;
        expect(link.getAttribute('href')).to.equal('#');

        const word = link.querySelector('.word');
        expect(word).to.exist;
        expect(word.textContent).to.equal(searchResult.word);
    });

    it('displays original word', () => {

        const newSearchResult = { ...searchResult, originalWord: 'original word' };
        const { container } = render(<SearchResult searchResult={newSearchResult} />);

        const word = container.querySelector('.word');
        expect(word).to.exist;
        expect(word.textContent).to.equal(newSearchResult.originalWord);

        const originalWord = container.querySelector('.development');
        expect(originalWord).to.exist;
        expect(originalWord.textContent).to.equal(newSearchResult.word);
    });

    it('selects the word', () => {
        const { container } = render(<SearchResult searchResult={searchResult} selected={true} />);

        const link = container.querySelector('a.selected');
        expect(link).to.exist;
        expect(link.querySelector('.word').textContent).to.equal(searchResult.word);
    });
});
