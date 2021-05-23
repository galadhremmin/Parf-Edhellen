import { IReduxAction } from '@root/_types';
import { IFragmentsReducerState } from './FragmentsReducer._types';

export type ISelectionReducerState = IFragmentsReducerState;

export interface ISelectionReducerAction extends IReduxAction {
    fragment: ISelectionReducerState;
}
