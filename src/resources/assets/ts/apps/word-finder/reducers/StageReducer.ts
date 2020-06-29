import {
    Actions,
    IGameAction,
    GameStage,
} from '../actions';
import { IStageReducerState } from './StageReducer._types';

const InitialState: IStageReducerState = {
    stage: GameStage.Loading,
};

const StageReducer = (state = InitialState, action: IGameAction) => {
    switch (action.type) {
        case Actions.InitializeGame:
        case Actions.SetStage:
            return {
                ...state,
                stage: action.stage,
            };
        default:
            return state;
    }
};

export default StageReducer;
