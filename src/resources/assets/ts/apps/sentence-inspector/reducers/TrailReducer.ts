import { Actions } from '../actions';
import type {
    ITrailReducerAction,
    ITrailReducerState,
} from './TrailReducer._types';

const TrailReducer = (state: ITrailReducerState = [], action: ITrailReducerAction) => {
    switch (action.type) {
        case Actions.RestoreTrail:
            return Array.isArray(action.trail) ? action.trail : [];

        case Actions.SelectFragment: {
            const fragmentId = action.fragmentId;
            if (! fragmentId || state.includes(fragmentId)) {
                return state;
            }
            return [ ...state, fragmentId ];
        }

        // A new phrase starts with an empty trail; the saved one arrives via RestoreTrail.
        case Actions.ReceiveSentence:
            return [];

        default:
            return state;
    }
};

export default TrailReducer;
