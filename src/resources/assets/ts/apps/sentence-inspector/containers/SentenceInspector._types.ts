import { ThunkDispatch } from 'redux-thunk';

import { IBookGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

import {
    FragmentsReducerState,
    IFragmentsReducerState,
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

export interface IState {
    fragment: IFragmentsReducerState;
    gloss: IBookGlossEntity;
}
