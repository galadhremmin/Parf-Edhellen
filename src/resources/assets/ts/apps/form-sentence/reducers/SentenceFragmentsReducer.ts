import { Actions as ValidationActions } from '@root/components/Form/Validation/Actions';
import type { IAction as IValidationAction } from '@root/components/Form/Validation/ValidationErrorReducer._types';
import ValidationError from '@root/connectors/ValidationError';
import { Actions } from '../actions';
import type {
    ISentenceFragmentsAction,
    ISentenceFragmentsReducerState,
} from './SentenceFragmentsReducer._types';
import SentenceFragmentReducer from './child-reducers/SentenceFragmentReducer';

const InitialState: ISentenceFragmentsReducerState = [];
const ErrorKeyRegEx = /^fragments\.(\d+)\./;

const SentenceFragmentsReducer = (state = InitialState, action: ISentenceFragmentsAction | IValidationAction) => {
    const sentenceAction = action as ISentenceFragmentsAction;
    const validationAction = action as IValidationAction;
    switch (action.type) {
        case Actions.ReloadAllFragments:
        case Actions.ReceiveFragment:
            return sentenceAction.sentenceFragments.map(
                (fragment, i) => SentenceFragmentReducer(null, {
                    ...sentenceAction,
                    sentenceFragment: {
                        _error: null,
                        paragraphNumber: 1,
                        sentenceNumber: 1,
                        ...fragment,
                        id: -(i + 1),
                    },
                }),
            );
        case Actions.SetFragment:
            return state.map((fragment) => {
                if (fragment.id === sentenceAction.sentenceFragment.id) {
                    return SentenceFragmentReducer(fragment, sentenceAction);
                }
                return fragment;
            });
        case Actions.SetFragmentField:
            return state.map((fragment) => {
                if (fragment.id === sentenceAction.sentenceFragment.id) {
                    return SentenceFragmentReducer(fragment, sentenceAction);
                }
                return fragment;
            });
        case ValidationActions.SetValidationErrors:
            if (validationAction.errors instanceof ValidationError) {
                const errorSet = new Map<number, string[]>();
                const err = validationAction.errors;
                for (const key of err.keys) {
                    const m = ErrorKeyRegEx.exec(key);
                    if (! m) {
                        continue;
                    }

                    const index = parseInt(m[1], 10);
                    if (errorSet.has(index)) {
                        errorSet.set(index, [ ...errorSet.get(index), ...err.errors[key] ]);
                    } else {
                        errorSet.set(index, err.errors[key]);
                    }
                }
                return state.map((fragment, i) => {
                    return SentenceFragmentReducer(fragment, {
                        type: Actions.SetFragmentField,
                        field: '_error',
                        sentenceFragment: fragment,
                        value: errorSet.get(i),
                    });
                });
            }
        // eslint-disable-next-line no-fallthrough
        default:
            return state;
    }
};

export default SentenceFragmentsReducer;
