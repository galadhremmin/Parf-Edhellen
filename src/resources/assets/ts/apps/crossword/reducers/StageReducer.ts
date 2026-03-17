import { dateNowInMilliseconds } from '@root/utilities/DateTime';
import { Actions, CrosswordStage, type ICrosswordAction } from '../actions';

export const MAX_CHECKS  = 3;
export const MAX_REVEALS = 3;

export interface IStageReducerState {
    stage: CrosswordStage;
    startTime: number;
    time: number;
    daysCompleted: number | null;
    secondsElapsed: number | null;
    isAssisted: boolean;
    checksRemaining: number;
    revealsRemaining: number;
}

const InitialState: IStageReducerState = {
    stage: CrosswordStage.Loading,
    startTime: 0,
    time: 0,
    daysCompleted: null,
    secondsElapsed: null,
    isAssisted: false,
    checksRemaining: MAX_CHECKS,
    revealsRemaining: MAX_REVEALS,
};

const StageReducer = (state = InitialState, action: ICrosswordAction): IStageReducerState => {
    switch (action.type) {
        case Actions.InitializePuzzle: {
            const now   = dateNowInMilliseconds();
            // Seed startTime with prior accumulated seconds so the Timer formula
            // (value - startValue) / 1000 naturally shows total elapsed time.
            const prior = (action.priorSeconds ?? 0) * 1000;
            return {
                ...InitialState,
                stage:     CrosswordStage.Playing,
                startTime: now - prior,
                time:      now,
            };
        }

        case Actions.ResumeTimer: {
            // Adjust startTime so elapsed time is preserved across tab switches.
            const now   = dateNowInMilliseconds();
            const prior = (action.priorSeconds ?? 0) * 1000;
            return { ...state, startTime: now - prior, time: now };
        }

        case Actions.UseCheck:
            return { ...state, checksRemaining: Math.max(0, state.checksRemaining - 1) };

        case Actions.UseReveal:
            return { ...state, revealsRemaining: Math.max(0, state.revealsRemaining - 1) };

        case Actions.SetStage:
            return { ...state, stage: action.stage ?? state.stage };

        case Actions.SetTime:
            return { ...state, time: action.time ?? state.time };

        case Actions.SetIsAssisted:
            return { ...state, isAssisted: action.isAssisted ?? state.isAssisted };

        case Actions.CompletionResult:
            return {
                ...state,
                stage: CrosswordStage.Complete,
                daysCompleted: action.daysCompleted ?? null,
                secondsElapsed: action.secondsElapsed ?? state.secondsElapsed,
                isAssisted: action.isAssisted ?? state.isAssisted,
            };

        default:
            return state;
    }
};

export default StageReducer;
