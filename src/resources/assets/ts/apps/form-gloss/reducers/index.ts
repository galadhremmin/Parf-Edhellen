import { combineReducers } from 'redux';

import { CreateRootReducer } from '@root/_types';
import { default as gloss } from './GlossReducer';

const reducers = {
    gloss,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
