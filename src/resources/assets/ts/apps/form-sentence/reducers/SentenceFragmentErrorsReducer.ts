import { Actions as ValidationActions } from '@root/components/Form/Validation/Actions';
import { IAction as IValidationAction } from '@root/components/Form/Validation/ValidationErrorReducer._types';
import ValidationError from '@root/connectors/ValidationError';
import { Actions } from '../actions';
import { ISentenceFragmentErrorsReducerState } from './SentenceFragmentErrorsReducer._types';

const InitialState: ISentenceFragmentErrorsReducerState = {}
const ErrorKeyRegEx = /^fragments\.(\d+)\./;

const SentenceFragmentErrorsReducer = (state = InitialState, action: IValidationAction) => {
    switch (action.type) {
        case ValidationActions.SetValidationErrors:
            if (action.errors instanceof ValidationError) {
                const newState: ISentenceFragmentErrorsReducerState = {};
                const errors = action.errors.errors;
                for (const key of errors.keys()) {
                    const m = ErrorKeyRegEx.exec(key);
                    if (! m) {
                        continue;
                    }

                    if (newState[m[1]] === undefined) {
                        newState[m[1]] = [errors.get(key)];
                    } else {
                        newState[m[1]].push(errors.get(key));
                    }
                }
                return newState;
            }
            break;
        case Actions.ReloadAllFragments:
            return InitialState;
    }

    return state;
};

export default SentenceFragmentErrorsReducer;
