import {
    ISentenceFragmentAction,
    ISentenceFragmentReducerState,
} from './child-reducers/SentenceFragmentReducer._types';

export type ISentenceFragmentsReducerState = ISentenceFragmentReducerState[];

export interface ISentenceFragmentsAction extends ISentenceFragmentAction {
    sentenceFragments: ISentenceFragmentsReducerState;
}
