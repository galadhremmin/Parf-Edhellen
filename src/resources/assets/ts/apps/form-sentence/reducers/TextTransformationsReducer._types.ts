import { IReduxAction } from '@root/_types';
import { ITextTransformationsMap, ITextTransformation } from '@root/connectors/backend/IBookApi';

export interface ITextTransformationAction extends IReduxAction {
    textTransformation?: ITextTransformation;
    textTransformations?: ITextTransformationsMap;
    transformerName?: string;
}

export type TextTransformationsReducerState = ITextTransformationsMap;
