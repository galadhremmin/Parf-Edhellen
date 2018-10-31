import { combineReducers } from 'redux';

import SearchReducer from './SearchReducer';
import SearchResultsReducer from './SearchResultsReducer';

import { ISearchState } from './SearchReducer._types';
import { ISearchResultState } from './SearchResultsReducer._types';

export interface IRootReducer {
    search: ISearchState;
    searchResults: ISearchResultState;
}

const rootReducer = combineReducers({
    search: SearchReducer,
    searchResults: SearchResultsReducer,
});

export default rootReducer;
