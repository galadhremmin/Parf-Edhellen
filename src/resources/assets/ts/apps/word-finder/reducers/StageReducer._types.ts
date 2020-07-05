import { GameStage } from '../actions';

export interface IStageReducerState {
    stage: GameStage;
    startTime: number;
    tengwarMode: string;
    time: number;
}