import { dateNowInMilliseconds } from '@root/utilities/DateTime';
import {
    Actions,
    type IGameAction,
    GameStage,
} from '../actions';
import type { IStageReducerState } from './StageReducer._types';

const InitialState: IStageReducerState = {
    stage: GameStage.Loading,
    startTime: 0,
    tengwarMode: null,
    time: 0,
};

const StageReducer = (state = InitialState, action: IGameAction) => {
    switch (action.type) {
        case Actions.InitializeGame: {
            const now = dateNowInMilliseconds();
            return {
                ...state,
                duration: 0,
                stage: action.stage,
                startTime: now,
                tengwarMode: action.language.tengwarMode || null,
                time: now,
            };
        }
        case Actions.SetStage:
            return {
                ...state,
                stage: action.stage,
            };
        case Actions.SetTime:
            return {
                ...state,
                time: action.time,
            };
        default:
            return state;
    }
};

export default StageReducer;
