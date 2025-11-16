import type { ComponentEventHandler } from '@root/components/Component._types';
import { GameStage } from './actions';
import type { IGameGloss } from './reducers/IGlossesReducer';
import type { IWordPart } from './reducers/PartsReducer._types';
import type { IStageReducerState } from './reducers/StageReducer._types';


export interface IContainerEvents extends IStageEvents {
    onLoadGame?: ComponentEventHandler<number>;
    onStageChange?: ComponentEventHandler<GameStage>;
    onTimeUpdate?: ComponentEventHandler<number>;
}

export interface IStageEvents {
    onDeselectPart?: ComponentEventHandler<number>;
    onDiscoverWord?: ComponentEventHandler<number>;
    onSelectPart?: ComponentEventHandler<number>;
}

export interface IGameProps {
    languageId: number;
}

export interface IContainerProps extends IGameProps, IContainerEvents {
    glosses?: IGameGloss[];
    parts?: IWordPart[];
    selectedParts?: number[];
    stage?: IStageReducerState;
    tengwarMode?: string;
}

export interface IStageProps extends IStageEvents {
    onChangeStage?: ComponentEventHandler<GameStage>;
    startTime?: number;
    time?: number;
}
