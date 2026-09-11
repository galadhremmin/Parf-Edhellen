import type { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';
import type { PhraseShape } from '../reducers/LinesReducer._types';

export interface IProps {
    fragment: IFragmentsReducerState;
    lineNumber: number;
    onSelectFragment: (fragmentId: number) => void;
    /** Position of this word among the words that can be opened, counted from one. */
    position: number;
    shape: PhraseShape;
    totalCount: number;
    /** The next word the reader has not opened yet, or 0 when there is none. */
    unopenedFragmentId: number;
    unopenedCount: number;
}
