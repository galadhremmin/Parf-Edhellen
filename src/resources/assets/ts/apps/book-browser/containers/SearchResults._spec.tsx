import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';

import '@root/utilities/Enzyme';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import { SearchResults } from './SearchResults';

describe('apps/book-browser/containers/SearchResults', () => {
    let wrapper: ReactWrapper;
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

    before(() => {
        wrapper = mount(<SearchResults searchGroups={groups} searchResults={searchResults} word={'word'}/>);
    });

    it(`was mounted with ${searchResults[0].length} search results`, () => {
        const list = wrapper.find('ul');
        expect(list).to.exist;

        const items = list.find('.search-result li');
        expect(items.length).to.equal(searchResults[0].length);

        const results = items.map((item) => item.find('SearchResult').prop('searchResult'));
        expect(results).to.deep.equal(searchResults[0]);
    });
});
