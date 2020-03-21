import { IReduxAction } from '@root/_types';
import { ParagraphState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';

export interface ILatinTextReducerState {
    dirty: boolean;
    paragraphs: ParagraphState[];
    text: string;
}

export interface ILatinTextAction extends IReduxAction {
    dirty: boolean;
    paragraphs: ParagraphState[];
    latinText: string;
}
