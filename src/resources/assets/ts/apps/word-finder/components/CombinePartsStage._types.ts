import { ComponentEventHandler } from '@root/components/Component._types';

import { IStageProps } from '../index._types';
import { IWordPart } from '../reducers/PartsReducer._types';

export interface IProps extends IStageProps {
    parts: IWordPart[];
    selectedParts: number[];
}