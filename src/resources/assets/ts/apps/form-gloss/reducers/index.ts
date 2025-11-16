import { combineReducers } from 'redux';

import type { CreateRootReducer } from '@root/_types';
import { ValidationErrorReducer as errors } from '@root/components/Form/Validation';
import { default as changes } from './ChangeTrackerReducer';
import { default as inflections } from './InflectionsReducer';
import { default as lexicalEntry } from './LexicalEntryReducer';

const reducers = {
    changes,
    errors,
    inflections,
    lexicalEntry,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
