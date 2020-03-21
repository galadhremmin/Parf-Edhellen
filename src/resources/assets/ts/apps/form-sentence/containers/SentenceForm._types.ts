import { ITextTransformationsMap } from '@root/connectors/backend/IBookApi';
import { IFragmentFormEvents } from '../components/FragmentsForm._types';
import { IMetadataFormEvents } from '../components/MetadataForm._types';
import { ISentenceTranslationReducerState } from '../reducers/child-reducers/SentenceTranslationReducer._types';
import { ILatinTextReducerState } from '../reducers/LatinTextReducer._types';
import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';

export type GlossProps = keyof ISentenceReducerState;

export interface ISentenceFieldChangeSpec {
    field: GlossProps;
    value: any;
}

export interface IProps extends IFragmentFormEvents, IMetadataFormEvents {
    prefetched?: boolean;
    sentence?: ISentenceReducerState;
    sentenceFragments?: ISentenceFragmentsReducerState;
    sentenceParagraphs?: ILatinTextReducerState['paragraphs'];
    sentenceText?: string;
    sentenceTextIsDirty?: boolean;
    sentenceTransformations: ITextTransformationsMap;
    sentenceTranslations?: ISentenceTranslationReducerState[];
}
