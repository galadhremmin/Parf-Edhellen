import { IReduxAction } from '@root/_types';

export interface ILatinTextAction extends IReduxAction {
    latinText: string;
}
