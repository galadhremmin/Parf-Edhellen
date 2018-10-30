import {
    Actions,
} from './constants';
import {
    ISearchResult,
    ISearchResultReducerAction,
    ISearchResultState,
} from './SearchResultsReducer._types';

const SearchResultsReducer = (state: ISearchResultState = [],
    action: ISearchResultReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSearchResults:
            return [
                ...action.searchResults,
            ];

        case Actions.SelectSearchResult:
            return select(state, action.id);

        case Actions.NextSearchResult: {
            if (state.length < 2) {
                return state;
            }

            const index = state.findIndex((r: ISearchResult) => r.selected);
            let newIndex = index + action.direction;
            if (newIndex < 0) {
                newIndex = state.length - 1;
            } else if (newIndex >= state.length) {
                newIndex = 0;
            }

            return select(state, state[newIndex].id);
        }
    }

    return state;
};

const select = (state: ISearchResultState, id: number) => //
    state.map((r: ISearchResult) => {
        const selected = r.id === id;
        if (r.selected === selected) {
            return r;
        }

        return { ...r, selected };
    });

export default SearchResultsReducer;
