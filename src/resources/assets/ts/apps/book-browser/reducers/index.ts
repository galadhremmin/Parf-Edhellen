import { combineReducers } from 'redux';

import { DeriveRootReducer } from '@root/_types';

import { default as glossary } from './GlossaryReducer';
import { default as glosses } from './GlossesReducer';
import { default as languages } from './LanguagesReducer';
import { default as search } from './SearchReducer';
import { default as searchResults } from './SearchResultsReducer';

const reducers = {
    glossary,
    glosses,
    languages,
    search,
    searchResults,
};

export type RootReducer = DeriveRootReducer<typeof reducers>;

export default combineReducers(reducers);
