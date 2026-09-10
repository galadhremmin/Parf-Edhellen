import type { DisplayMode, IDisplayReducerState } from '../reducers/DisplayReducer._types';

import type { ReactNode } from 'react';

export interface IProps {
    /** The phrase-level "keep these words" action, when the reader is signed in. */
    action?: ReactNode;
    display: IDisplayReducerState;
    /** Disabled with a reason rather than silently dead when the phrase has no English. */
    hasTranslations: boolean;
    onToggle: (mode: DisplayMode) => void;
    openedCount: number;
    totalCount: number;
}
