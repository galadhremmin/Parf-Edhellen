import type { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';

export interface IProps {
    fragment: IFragmentsReducerState;
    /** True when the reader has asked to see which forms differ from their headword. */
    highlightChanged?: boolean;
    isOpened: boolean;
    isSelected: boolean;
    onClick: (fragmentId: number) => void;
    /** The tengwar line repeats every word; only one of the two should carry the marks. */
    plain?: boolean;
    text: string;
}
