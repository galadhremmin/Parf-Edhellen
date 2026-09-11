import type { DisplayMode, IDisplayReducerState } from '../reducers/DisplayReducer._types';

import type { ReactNode } from 'react';

export interface IProps {
    /** The phrase-level "keep these words" action, when the reader is signed in. */
    action?: ReactNode;
    display: IDisplayReducerState;
    /** When false the English toggle is replaced by a notice explaining the gap. */
    hasTranslations: boolean;
    isSignedIn: boolean;
    onToggle: (mode: DisplayMode) => void;
    openedCount: number;
    sentenceId: number;
    totalCount: number;
}
