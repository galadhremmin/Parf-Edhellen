import { IFragmentInSentenceState } from '../reducers/FragmentsReducer._types';

export interface IProps {
    fragment: IFragmentInSentenceState;
    onClick?: (fragment: IFragmentInSentenceState) => void;
    selected?: boolean;
}
