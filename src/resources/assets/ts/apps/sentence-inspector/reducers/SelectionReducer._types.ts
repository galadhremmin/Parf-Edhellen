import { IReduxAction } from "@root/_types";

export interface ISelectionReducerState {
    fragmentId: number;
    sentenceNumber: number;
}

export interface ISelectionReducerAction extends IReduxAction {
    fragmentId?: number;
    sentenceNumber?: number;
}