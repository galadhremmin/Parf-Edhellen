import { Factory } from 'react';

import {
    IFragmentInSentenceState,
    ITextState,
} from '../reducers/FragmentsReducer._types';
import { IProps as IFragmentInspectorProps } from './FragmentInspector._types';

export interface IProps {
    fragmentInspector: Factory<IFragmentInspectorProps>;
    fragmentId: number;
    texts: ITextState[];
    onFragmentClick?: (fragment: IFragmentInSentenceState) => void;
}

export interface IRenderArgs {
    fragmentSelected: boolean;
    paragraphNumber: number;
}
