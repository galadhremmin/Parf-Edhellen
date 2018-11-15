import {
    FragmentsReducerState,
    ITextState,
} from '../reducers/FragmentsReducer._types';
import { ISelectionReducerState } from '../reducers/SelectionReducer._types';
import { TranslationsState } from '../reducers/TranslationsReducer._types';

export interface IProps {
    fragments: FragmentsReducerState;
    latinFragments: ITextState;
    selection: ISelectionReducerState;
    tengwarFragments: ITextState;
    translations: TranslationsState;
}

export interface IState {
    leftHand: ITextState[];
    rightHand: ITextState[];
}
