import { Actions } from '../actions';
import { ILatinTextAction } from './LatinTextReducer._types';

const InitialState = '';

const LatinTextReducer = (state = InitialState, action: ILatinTextAction) => {
    switch (action.type) {
        case Actions.SetLatinText:
            return action.latinText;
        default:
            return state;
    }
};

export default LatinTextReducer;
