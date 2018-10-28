import {
    Actions,
} from './constants';
import {
    ISearchResult,
    ISearchResultReducerAction,
    ISearchResultState,
} from './SearchResultsReducer.types';

const SearchResultsReducer = (state: ISearchResultState = [],
    action: ISearchResultReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSearchResults:
            return [
                ...action.searchResults,
            ];
        case Actions.SelectSearchResult:
            return state.map((r: ISearchResult) => {
                const selected = r.id === action.id;
                if (r.selected === selected) {
                    return r;
                }

                return { ...r, selected };
            });
    }

    return state;
};

export default SearchResultsReducer;
