import { IReduxAction } from '@root/_types';

export interface ISentenceTextAction extends IReduxAction {
    text: string;
}
