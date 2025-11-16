import type { IStageProps } from '../index._types';
import type { IWordPart } from '../reducers/PartsReducer._types';

export interface IProps extends IStageProps {
    parts: IWordPart[];
    selectedParts: number[];
    tengwarMode: string;
}