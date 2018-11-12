import { combineReducers } from 'redux';

import FragmentsReducer from './FragmentsReducer';
import LatinFragmentsReducer from './LatinFragmentsReducer';
import SentenceReducer from './SentenceReducer';
import TengwarFragmentsReducer from './TengwarFragmentsReducer';

const rootReducer = combineReducers({
    fragments: FragmentsReducer,
    latinFragments: LatinFragmentsReducer,
    sentence: SentenceReducer,
    tengwarFragments: TengwarFragmentsReducer,
});

export default rootReducer;
