import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';

export type GlossProps = keyof ISentenceReducerState;

export interface ISentenceFieldChangeSpec {
    field: GlossProps;
    value: any;
}

export interface IProps {
    onSentenceFieldChange: ComponentEventHandler<ISentenceFieldChangeSpec>;
    prefetched?: boolean;
    sentence?: ISentenceReducerState;
    sentenceFragments?: ISentenceFragmentsReducerState;
    sentenceTranslations?: null[];
}
