import {
    describe,
    expect,
    test,
} from '@jest/globals';

import { Actions } from '../actions';
import SearchResultsReducer from './SearchResultsReducer';
import type { ISearchResult } from './SearchResultsReducer._types';

describe('apps/book-browser/reducers/SearchResultsReducer', () => {
    test('builds correct state', () => {
        const keywords: Record<string, ISearchResult[]> = {};
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
        keywords[groupName] = values;

        const actual = SearchResultsReducer({
            groups: [],
            resultIds: [],
            resultsByGroupIndex: [],
            resultsById: {},
            selectedId: null,
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
        expect(actual.selectedId).toBeNull();
    });

    test('does not select a search result until one is picked', () => {
        const values: ISearchResult[] = [
            {
                id: 0,
                normalizedWord: 'elf',
                originalWord: null,
                word: 'elf',
            },
        ];

        const state = SearchResultsReducer({
            groups: [],
            resultIds: [],
            resultsByGroupIndex: [],
            resultsById: {},
            selectedId: null,
            groupIdMap: {},
        }, {
            searchResults: {
                keywords: { 'unit test': values },
                searchGroups: {},
            },
            type: Actions.ReceiveSearchResults,
        });

        // The first result carries the ID 0, which must not be mistaken for a selection.
        expect(state.selectedId).toBeNull();

        const selected = SearchResultsReducer(state, {
            id: 0,
            type: Actions.SelectSearchResult,
        });
        expect(selected.selectedId).toEqual(0);
    });
});
