import { combineReducers } from 'redux';
import type { CreateRootReducer } from '@root/_types';

import { default as cells } from './CellsReducer';
import { default as check } from './CheckReducer';
import { default as puzzle } from './PuzzleReducer';
import { default as selection } from './SelectionReducer';
import { default as stage } from './StageReducer';

const reducers = {
    cells,
    check,
    puzzle,
    selection,
    stage,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
