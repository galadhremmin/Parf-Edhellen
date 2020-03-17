import { IReduxAction } from '@root/_types';

export interface ILatinTextReducerState {
    dirty: boolean;
    text: string;
}

export interface ILatinTextAction extends IReduxAction {
    dirty: boolean;
    latinText: string;
}
