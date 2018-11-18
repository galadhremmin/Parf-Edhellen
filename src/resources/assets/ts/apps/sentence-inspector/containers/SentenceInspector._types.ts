import { ThunkDispatch } from 'redux-thunk';
import {
    FragmentsReducerState,
    ITextState,
} from '../reducers/FragmentsReducer._types';
import { ISelectionReducerState } from '../reducers/SelectionReducer._types';
import { TranslationsState } from '../reducers/TranslationsReducer._types';

export interface IProps {
    dispatch?: ThunkDispatch<any, any, any>;
    fragments: FragmentsReducerState;
    latinFragments: ITextState;
    selection: ISelectionReducerState;
    tengwarFragments: ITextState;
    translations: TranslationsState;
}
