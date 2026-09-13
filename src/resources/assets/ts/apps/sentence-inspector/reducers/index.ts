import { combineReducers } from 'redux';

import type { CreateRootReducer } from '@root/_types';

import DisplayReducer from './DisplayReducer';
import FragmentsReducer from './FragmentsReducer';
import LinesReducer from './LinesReducer';
import SelectionReducer from './SelectionReducer';
import SentenceReducer from './SentenceReducer';
import TrailReducer from './TrailReducer';

const reducers = {
    display: DisplayReducer,
    fragments: FragmentsReducer,
    lines: LinesReducer,
    selection: SelectionReducer,
    sentence: SentenceReducer,
    trail: TrailReducer,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
