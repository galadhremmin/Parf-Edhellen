import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';

import '@root/utilities/Enzyme';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import SearchResult from './SearchResult';

describe('apps/book-browser/components/SearchResultsContainer', () => {
    const searchResult: ISearchResult = {
        id: 1,
        normalizedWord: 'normalized word',
        originalWord: null,
        selected: false,
        word: 'word',
    };

    let wrapper: ReactWrapper = null;

    before(() => {
        wrapper = mount(<SearchResult searchResult={searchResult} />);
    });

    it('displays a word', () => {
        const link = wrapper.find('a');
        expect(link).to.exist;
        expect(link.prop('href')).to.equal('#');

        const word = link.find('.word');
        expect(word).to.exist;
        expect(word.text()).to.equal(searchResult.word);
    });

    it('displays original word', () => {
        const newSearchResult = { ...searchResult, originalWord: 'original word' };
        wrapper.setProps({ searchResult: newSearchResult });

        const word = wrapper.find('.word');
        expect(word).to.exist;
        expect(word.text()).to.equal(newSearchResult.originalWord);

        const originalWord = wrapper.find('.development');
        expect(originalWord).to.exist;
        expect(originalWord.text()).to.equal(newSearchResult.word);
    });

    it('selects the word', () => {
        const newSearchResult = { ...searchResult, selected: true };
        wrapper.setProps({ searchResult: newSearchResult });

        const link = wrapper.find('a.selected');
        expect(link).to.exist;
    });
});
