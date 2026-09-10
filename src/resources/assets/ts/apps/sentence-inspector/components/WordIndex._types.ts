import type { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';
import type { PhraseShape } from '../reducers/LinesReducer._types';

export type WordFilter = 'all' | 'note' | 'changed' | 'unopened';

export interface IProps {
    /** Fragment id → the ordinal of the line it stands in. */
    lineNumbers: Map<number, number>;
    onSelectFragment: (fragmentId: number) => void;
    selectedFragmentId: number;
    shape: PhraseShape;
    trail: Set<number>;
    words: IFragmentsReducerState[];
}
