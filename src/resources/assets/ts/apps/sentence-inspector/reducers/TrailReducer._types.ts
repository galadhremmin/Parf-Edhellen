import type { IReduxAction } from '@root/_types/redux';

/**
 * The words the reader has opened, so the text can show where they have been. Kept as an
 * array rather than a Set because reducer state should be plain and serialisable.
 */
export type ITrailReducerState = number[];

export interface ITrailReducerAction extends IReduxAction {
    fragmentId?: number;
    trail?: ITrailReducerState;
}
