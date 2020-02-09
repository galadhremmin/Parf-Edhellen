import { Actions } from '../actions';
import {
    ISentenceTextAction,
} from './SentenceTextReducer._types';

const InitialState = '';

const SentenceReducer = (state: string = InitialState, action: ISentenceTextAction) => {
    switch (action.type) {
        case Actions.ReceiveFragment:
            return '';
        case Actions.SetText:
            return action.text;
        default:
            return state;
    }
};

export default SentenceReducer;
