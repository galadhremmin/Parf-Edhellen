import { ComponentEventHandler } from '@root/components/Component._types';
import {
    IFragmentInSentenceState,
    IFragmentsReducerState,
    ITextState,
} from '../reducers/FragmentsReducer._types';
import { IEventProps as IInspectorEventProps } from './FragmentInspector._types';

export interface IProps extends IEventProps {
    fragment: IFragmentsReducerState;
    texts: ITextState[];
}

export interface IEventProps extends IInspectorEventProps {
    onFragmentInSentenceClick?: ComponentEventHandler<IFragmentInSentenceState>;
}

export interface IRenderArgs {
    fragmentSelected: boolean;
    paragraphNumber: number;
}