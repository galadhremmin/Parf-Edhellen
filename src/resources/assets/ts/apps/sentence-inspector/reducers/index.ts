import { combineReducers } from 'redux';

import {
    FragmentsReducerState,
    LocalizedFragmentsReducerState,
} from './FragmentsReducer._types';
import { ISelectionReducerState } from './SelectionReducer._types';
import { ISentenceReducerState } from './SentenceReducer._types';
import { TranslationsState } from './TranslationsReducer._types';

import FragmentsReducer from './FragmentsReducer';
import LatinFragmentsReducer from './LatinFragmentsReducer';
import SelectionReducer from './SelectionReducer';
import SentenceReducer from './SentenceReducer';
import TengwarFragmentsReducer from './TengwarFragmentsReducer';
import TranslationsReducer from './TranslationsReducer';

export interface IRootReducer {
    fragments: FragmentsReducerState;
    latinFragments: LocalizedFragmentsReducerState;
    selection: ISelectionReducerState;
    sentence: ISentenceReducerState;
    tengwarFragments: LocalizedFragmentsReducerState;
    translations: TranslationsState;
}

const rootReducer = combineReducers({
    fragments: FragmentsReducer,
    latinFragments: LatinFragmentsReducer,
    selection: SelectionReducer,
    sentence: SentenceReducer,
    tengwarFragments: TengwarFragmentsReducer,
    translations: TranslationsReducer,
});

export default rootReducer;
