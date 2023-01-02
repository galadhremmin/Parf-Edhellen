import {
    describe,
    expect,
    test,
} from '@jest/globals';

import { Actions } from '../actions';
import SearchResultsReducer from './SearchResultsReducer';
import { ISearchResult } from './SearchResultsReducer._types';

describe('apps/book-browser/reducers/SearchResultsReducer', () => {
    test('builds correct state', () => {
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

        expect(actual.groups).toHaveLength(1);
        expect(actual.groups).toContain(groupName);
        expect(actual.resultIds).toEqual(values.map((v) => v.id));
        expect(actual.resultsByGroupIndex).toEqual([values]);
        expect(actual.resultsById).toEqual(values.reduce((carry, v) => {
            carry[v.id] = v;
            return carry;
        }, {} as any));
        expect(actual.selectedId).toEqual(0);
    });
});
