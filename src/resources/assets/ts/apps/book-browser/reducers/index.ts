import { combineReducers } from 'redux';

import type { CreateRootReducer } from '@root/_types';

import { default as categories } from './CategoriesReducer';
import { default as entities } from './EntitiesReducer';
import { default as sections } from './SectionsReducer';
import { default as search } from './SearchReducer';
import { default as searchResults } from './SearchResultsReducer';

const reducers = {
    categories,
    entities,
    sections,
    search,
    searchResults,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
