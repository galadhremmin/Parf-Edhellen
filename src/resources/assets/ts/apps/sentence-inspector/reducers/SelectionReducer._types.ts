import type { IReduxAction } from '@root/_types';
import type { IFragmentsReducerState } from './FragmentsReducer._types';

export type ISelectionReducerState = IFragmentsReducerState;

export interface ISelectionReducerAction extends IReduxAction {
    fragment: ISelectionReducerState;
}
