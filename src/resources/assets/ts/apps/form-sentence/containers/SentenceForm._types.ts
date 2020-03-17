import { IFragmentFormEvents } from '../components/FragmentsForm._types';
import { IMetadataFormEvents } from '../components/MetadataForm._types';
import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';
import { TextTransformationsReducerState } from '../reducers/TextTransformationsReducer._types';

export type GlossProps = keyof ISentenceReducerState;

export interface ISentenceFieldChangeSpec {
    field: GlossProps;
    value: any;
}

export interface IProps extends IFragmentFormEvents, IMetadataFormEvents {
    prefetched?: boolean;
    sentence?: ISentenceReducerState;
    sentenceFragments?: ISentenceFragmentsReducerState;
    sentenceTransformations?: TextTransformationsReducerState;
    sentenceText?: string;
    sentenceTextIsDirty?: boolean;
    sentenceTranslations?: null[];
}
