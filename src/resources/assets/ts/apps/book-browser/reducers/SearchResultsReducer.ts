import Actions from './Actions';
import {
    ISearchResult,
    ISearchResultReducerAction,
    ISearchResultState,
} from './SearchResultsReducer._types';

const SearchResultsReducer = (state: ISearchResultState = [],
    action: ISearchResultReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSearchResults: {
            const searchResults = _transitionSelected(state, action.searchResults);
            return [
                ...searchResults,
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

export default SearchResultsReducer;
