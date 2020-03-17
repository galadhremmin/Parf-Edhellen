import { Actions } from '../actions';
import {
    ILatinTextAction,
    ILatinTextReducerState,
} from './LatinTextReducer._types';

const InitialState: ILatinTextReducerState = {
    dirty: true,
    text: '',
};

const LatinTextReducer = (state = InitialState, action: ILatinTextAction) => {
    switch (action.type) {
        case Actions.SetLatinText:
            return {
                dirty: action.dirty,
                text: action.latinText,
            };
        default:
            return state;
    }
};

export default LatinTextReducer;
