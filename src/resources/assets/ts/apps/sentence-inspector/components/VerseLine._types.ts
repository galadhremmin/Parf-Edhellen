import type { IDisplayReducerState } from '../reducers/DisplayReducer._types';
import type { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';
import type { ILine } from '../reducers/LinesReducer._types';

export interface IProps {
    display: IDisplayReducerState;
    fragments: Map<number, IFragmentsReducerState>;
    isActive: boolean;
    line: ILine;
    onSelectFragment: (fragmentId: number) => void;
    selectedFragmentId: number;
    trail: Set<number>;
}
