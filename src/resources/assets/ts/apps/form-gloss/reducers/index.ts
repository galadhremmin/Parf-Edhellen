import { combineReducers } from 'redux';

import { CreateRootReducer } from '@root/_types';
import { ValidationErrorReducer as errors } from '@root/components/Form/Validation';
import { default as inflections } from './InflectionsReducer';
import { default as gloss } from './GlossReducer';

const reducers = {
    errors,
    inflections,
    gloss,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
