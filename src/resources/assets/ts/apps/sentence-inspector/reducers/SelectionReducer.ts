import Actions from './Actions';
import { ISelectionReducerState } from './SelectionReducer._types';
import { ISentenceReducerAction } from './SentenceReducer._types';

const SelectionReducer = (state: ISelectionReducerState = {
    fragmentId: 0,
    sentenceNumber: 0,
}, action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.SelectFragment:
            return {
                ...state,
                fragmentId: action.id,
            };
        default:
            return state;
    }
};

export default SelectionReducer;
