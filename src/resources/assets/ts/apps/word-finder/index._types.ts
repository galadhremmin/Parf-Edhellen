import { ComponentEventHandler } from '@root/components/Component._types';
import { GameStage } from './actions';
import { IGameGloss } from './reducers/IGlossesReducer';
import { IWordPart } from './reducers/PartsReducer._types';
import { IStageReducerState } from './reducers/StageReducer._types';


export interface IContainerEvents extends IStageEvents {
    onLoadGame?: ComponentEventHandler<number>;
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
}

export interface IStageProps extends IStageEvents {
    onChangeStage?: ComponentEventHandler<GameStage>;
}
