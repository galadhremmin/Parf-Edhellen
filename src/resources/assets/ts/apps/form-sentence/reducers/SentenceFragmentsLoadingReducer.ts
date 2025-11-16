import type { IReduxAction } from '@root/_types';
import { Actions as ValidationActions } from '@root/components/Form/Validation/Actions';
import { Actions } from '../actions';

const SentenceFragmentsLoadingReducer = (state = false, action: IReduxAction<Actions | ValidationActions>) => {
    switch (action.type) {
        case Actions.RequestTransformation:
            return true;
        case Actions.ReloadAllFragments:
        case ValidationActions.SetValidationErrors:
            return false;
        default:
            return state;
    }
};

export default SentenceFragmentsLoadingReducer;
