import { Actions } from '../actions';
import SentenceFragmentReducer from './child-reducers/SentenceFragmentReducer';
import {
    ISentenceFragmentsAction,
    ISentenceFragmentsReducerState,
} from './SentenceFragmentsReducer._types';

const InitialState: ISentenceFragmentsReducerState = [];

const SentenceFragmentsReducer = (state = InitialState, action: ISentenceFragmentsAction) => {
    switch (action.type) {
        case Actions.ReceiveFragment:
            return action.sentenceFragments.map(
                (fragment, i) => SentenceFragmentReducer(fragment, {
                    ...action,
                    sentenceFragment: {
                        ...fragment,
                        id: (i + 1) * 10,
                    },
                }),
            );
        case Actions.SetFragment:
            return state.map((fragment) => {
                if (fragment.id === action.sentenceFragment.id) {
                    return SentenceFragmentReducer(fragment, action);
                }
                return state;
            });
        default:
            return state;
    }
};

export default SentenceFragmentsReducer;
