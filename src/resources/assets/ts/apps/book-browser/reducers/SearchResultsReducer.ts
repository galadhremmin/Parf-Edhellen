import {
    Actions,
} from './constants';
import {
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
    }

    return state;
};

export default SearchResultsReducer;
