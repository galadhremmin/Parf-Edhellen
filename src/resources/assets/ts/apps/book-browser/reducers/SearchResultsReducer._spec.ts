import { expect } from 'chai';

import { Actions } from '../actions';
import SearchResultsReducer from './SearchResultsReducer';
import { ISearchResult } from './SearchResultsReducer._types';

describe('apps/book-browser/reducers/SearchResultsReducer', () => {
    it ('builds correct state', () => {
        const keywords = new Map<string, ISearchResult[]>();
        const groupName = 'unit test';
        const values = [
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
        keywords.set(groupName, values);

        const actual = SearchResultsReducer({
            groups: [],
            resultIds: [],
            resultsByGroupIndex: [],
            resultsById: {},
            selectedId: 0,
            groupIdMap: {},
        }, {
            searchResults: {
                keywords,
                searchGroups: {},
            },
            type: Actions.ReceiveSearchResults,
        });

        expect(actual.groups).to.have.lengthOf(1);
        expect(actual.groups).to.contain(groupName);
        expect(actual.resultIds).to.deep.equal(values.map((v) => v.id));
        expect(actual.resultsByGroupIndex).to.deep.equal([values]);
        expect(actual.resultsById).to.deep.equal(values.reduce((carry, v) => {
            carry[v.id] = v;
            return carry;
        }, {} as any));
        expect(actual.selectedId).to.equal(0);
    });
});
