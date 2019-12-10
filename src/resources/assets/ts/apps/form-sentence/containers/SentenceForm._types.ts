import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';

export interface IProps {
    sentence?: ISentenceReducerState;
    sentenceFragments?: ISentenceFragmentsReducerState;
    sentenceTranslations?: null[];
    prefetched?: boolean;
}
