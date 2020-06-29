import { combineReducers } from 'redux';
import { CreateRootReducer } from '@root/_types';

import { default as glosses } from './GlossesReducer';
import { default as parts } from './PartsReducer';
import { default as selectedParts } from './SelectedPartsReducer';
import { default as stage } from './StageReducer';

const reducers = {
    glosses,
    parts,
    selectedParts,
    stage,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
