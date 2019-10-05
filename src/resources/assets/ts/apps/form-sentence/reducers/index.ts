import { combineReducers } from 'redux';

import { CreateRootReducer } from '@root/_types';
import { ValidationErrorReducer as errors } from '@root/components/Form/Validation';

const reducers = {
    errors,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
