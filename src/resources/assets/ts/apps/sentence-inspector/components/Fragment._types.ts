import { ComponentEventHandler } from '@root/components/Component._types';
import { IFragmentInSentenceState } from '../reducers/FragmentsReducer._types';

export interface IProps {
    fragment: IFragmentInSentenceState;
    onClick?: ComponentEventHandler<IFragmentInSentenceState>;
    selected?: boolean;
}
