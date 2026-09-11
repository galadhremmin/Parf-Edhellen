import type { DisplayMode, IDisplayReducerState } from '../reducers/DisplayReducer._types';
import type { FragmentsReducerState, IFragmentsReducerState } from '../reducers/FragmentsReducer._types';
import type { ILinesReducerState } from '../reducers/LinesReducer._types';
import type { ISelectionReducerState } from '../reducers/SelectionReducer._types';
import type { ISentenceReducerState } from '../reducers/SentenceReducer._types';
import type { ITrailReducerState } from '../reducers/TrailReducer._types';

export interface IProps extends IEventProps {
    display: IDisplayReducerState;
    fragments: FragmentsReducerState;
    lines: ILinesReducerState;
    selection: ISelectionReducerState;
    sentence: ISentenceReducerState;
    trail: ITrailReducerState;
}

export interface IEventProps {
    onFragmentSelect?: (fragment: IFragmentsReducerState, sentenceId: number) => void;
    onToggleDisplay?: (mode: DisplayMode) => void;
}
