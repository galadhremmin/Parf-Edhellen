import type { ComponentEventHandler } from '@root/components/Component._types';
import type { IFragmentInSentenceState } from '../reducers/FragmentsReducer._types';

export interface IProps {
    fragment: IFragmentInSentenceState;
    onClick?: ComponentEventHandler<IFragmentInSentenceState>;
    selected?: boolean;
}
