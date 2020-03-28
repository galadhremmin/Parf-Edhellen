import { Actions } from '../actions';
import {
    ITextTransformationAction,
    TextTransformationsReducerState,
} from './TextTransformationsReducer._types';

const InitialState: TextTransformationsReducerState = {};

const TextTransformationsReducer = (state = InitialState, action: ITextTransformationAction) => {
    switch (action.type) {
        case Actions.ReloadAllFragments:
        case Actions.ReceiveTransformation:
            return action.textTransformations;
        default:
            return state;
    }
};

export default TextTransformationsReducer;
