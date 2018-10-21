import { combineReducers } from 'redux';

import SearchReducer from './SearchReducer';
import SearchResultsReducer from './SearchResultsReducer';

const rootReducer = combineReducers({
    search: SearchReducer,
    searchResults: SearchResultsReducer,
});

export default rootReducer;
