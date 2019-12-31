import { Actions } from '../actions';
import {
    ISearchResult,
    ISearchResultReducerAction,
    ISearchResultState,
} from './SearchResultsReducer._types';

/**
 * Transitions the `selected` state from current state to the new array of search results.
 * @param state current state
 * @param results new state
 */
const _transitionSelected = (state: ISearchResultState, results: ISearchResult[]) => {
    const selected = state.find((r) => r.selected) || null;
    if (selected !== null) {
        const newSelected = results.find((r) => r.id === selected.id) || null;

        if (newSelected !== null) {
            newSelected.selected = true;
        }
    }

    return results;
};

const _filterResults = (keywords: ISearchResult[]) => {
    const usedKeywords = new Set();
    const results = [];

    for (const keyword of keywords) {
        if (! usedKeywords.has(keyword.word)) {
            usedKeywords.add(keyword.word);
            results.push(keyword);
        } else if (keyword.word !== keyword.originalWord) {
            results.push(keyword);
        }
    }

    usedKeywords.clear();
    return results;
};

const SearchResultsReducer = (state: ISearchResultState = [],
    action: ISearchResultReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSearchResults: {
            const searchResults = _transitionSelected(state, action.searchResults);
            return [
                ..._filterResults(searchResults),
            ];
        }

        case Actions.SelectSearchResult:
            return state.map((r: ISearchResult) => {
                const selected = r.id === action.id;
                if (r.selected === selected) {
                    return r;
                }

                return { ...r, selected };
            });

        default:
            return state;
    }
};

export default SearchResultsReducer;
