import { combineReducers } from 'redux';

import FragmentsReducer from './FragmentsReducer';
import LatinFragmentsReducer from './LatinFragmentsReducer';
import TengwarFragmentsReducer from './TengwarFragmentsReducer';

const rootReducer = combineReducers({
    fragments: FragmentsReducer,
    latinFragments: LatinFragmentsReducer,
    tengwarFragments: TengwarFragmentsReducer,
});

export default rootReducer;
