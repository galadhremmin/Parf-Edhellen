import { Actions } from '../actions';
import type {
    ILatinTextAction,
    ILatinTextReducerState,
} from './LatinTextReducer._types';

const InitialState: ILatinTextReducerState = {
    dirty: true,
    paragraphs: [],
    text: '',
};

const LatinTextReducer = (state = InitialState, action: ILatinTextAction) => {
    switch (action.type) {
        case Actions.ReloadAllFragments:
        case Actions.SetLatinText:
            return {
                dirty: action.dirty,
                paragraphs: action.paragraphs,
                text: action.latinText,
            } as ILatinTextReducerState;
        default:
            return state;
    }
};

export default LatinTextReducer;
