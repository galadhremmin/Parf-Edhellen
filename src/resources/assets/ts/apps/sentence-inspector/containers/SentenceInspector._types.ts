import {
    FragmentsReducerState,
    LocalizedFragmentsReducerState,
} from '../reducers/FragmentsReducer._types';
import { ISelectionReducerState } from '../reducers/SelectionReducer._types';
import { TranslationsState } from '../reducers/TranslationsReducer._types';

export interface IProps {
    fragments: FragmentsReducerState;
    latinFragments: LocalizedFragmentsReducerState;
    selection: ISelectionReducerState;
    tengwarFragments: LocalizedFragmentsReducerState;
    translations: TranslationsState;
}

export interface IState {
    leftHand: LocalizedFragmentsReducerState[];
    rightHand: LocalizedFragmentsReducerState[];
}
