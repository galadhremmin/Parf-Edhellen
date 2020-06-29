import {
    Actions,
    IGameAction,
} from '../actions';

const InitialState: number[] = [];

const SelectedPartsReducer = (state = InitialState, action: IGameAction) => {
    switch (action.type) {
        case Actions.SelectPart:
            return state.indexOf(action.selectedPartId) === -1 //
                ? [ ...state, action.selectedPartId ] //
                : state;
        case Actions.DeselectPart:
            return state.filter((partId) => partId !== action.selectedPartId);
        case Actions.DiscoverWord:
            return [];
        default:
            return state;
    }
};

export default SelectedPartsReducer;
