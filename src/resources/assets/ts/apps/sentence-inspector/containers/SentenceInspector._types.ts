import type { ComponentEventHandler } from '@root/components/Component._types';

import type {
    FragmentsReducerState,
    IFragmentsReducerState,
    ITextState,
} from '../reducers/FragmentsReducer._types';
import type { ISelectionReducerState } from '../reducers/SelectionReducer._types';
import type{ TranslationsState } from '../reducers/TranslationsReducer._types';

export interface IProps extends IEventProps {
    fragments: FragmentsReducerState;
    latinFragments: ITextState;
    selection: ISelectionReducerState;
    tengwarFragments: ITextState;
    translations: TranslationsState;
}

export interface IEventProps {
    onFragmentSelect?: ComponentEventHandler<IFragmentsReducerState>;
}
