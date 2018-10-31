import { combineReducers } from 'redux';

import GlossaryReducer from './GlossaryReducer';
import GlossesReducer from './GlossesReducer';
import LanguagesReducer from './LanguagesReducer';
import SearchReducer from './SearchReducer';
import SearchResultsReducer from './SearchResultsReducer';

import { IGlossaryState } from './GlossaryReducer._types';
import { IGlossesState } from './GlossesReducer._types';
import { LanguagesState } from './LanguagesReducer._types';
import { ISearchState } from './SearchReducer._types';
import { ISearchResultState } from './SearchResultsReducer._types';

export interface IRootReducer {
    glossary: IGlossaryState;
    glosses: IGlossesState;
    languages: LanguagesState;
    search: ISearchState;
    searchResults: ISearchResultState;
}

const rootReducer = combineReducers({
    glossary: GlossaryReducer,
    glosses: GlossesReducer,
    languages: LanguagesReducer,
    search: SearchReducer,
    searchResults: SearchResultsReducer,
});

export default rootReducer;
