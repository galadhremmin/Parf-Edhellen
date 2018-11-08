import { IReduxAction } from '@root/_types/redux';

export interface IFragmentsReducerState {
    comments: string;
    glossId: number;
    id: number;
    sentenceNumber: number;
    speechId: number;
    type: number;
}

export type FragmentsReducerState = IFragmentsReducerState[];

export interface IFragmentsReducerAction extends IReduxAction {
    id: number;
}
