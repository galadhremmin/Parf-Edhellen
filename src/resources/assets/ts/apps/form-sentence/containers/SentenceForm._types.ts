import type { ComponentEventHandler } from '@root/components/Component._types';
import ValidationError from '@root/connectors/ValidationError';
import type IBookApi from '@root/connectors/backend/IBookApi';
import type { ITextTransformationsMap } from '@root/connectors/backend/IBookApi';
import type { ISaveSentenceContributionEntity } from '@root/connectors/backend/IContributionResourceApi';
import type { IFragmentFormEvents } from '../components/FragmentsForm._types';
import type { IMetadataFormEvents } from '../components/MetadataForm._types';
import type { ITranslationFormEvents } from '../components/TranslationForm/TranslationForm._types';
import type { ILatinTextReducerState } from '../reducers/LatinTextReducer._types';
import type { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import type { ISentenceReducerState } from '../reducers/SentenceReducer._types';
import type { ISentenceTranslationReducerState } from '../reducers/child-reducers/SentenceTranslationReducer._types';

export type GlossProps = keyof ISentenceReducerState;

export interface ISentenceFieldChangeSpec {
    field: GlossProps;
    value: any;
}

export interface ISentenceFormEvents {
    onSubmit: ComponentEventHandler<ISaveSentenceContributionEntity>;
}

export interface IProps extends Partial<ISentenceFormEvents>, Partial<IFragmentFormEvents>, Partial<IMetadataFormEvents>, Partial<ITranslationFormEvents> {
    bookApi?: IBookApi;
    errors?: ValidationError;
    prefetched?: boolean;
    sentence?: ISentenceReducerState;
    sentenceFragments?: ISentenceFragmentsReducerState;
    sentenceFragmentsLoading?: boolean;
    sentenceParagraphs?: ILatinTextReducerState['paragraphs'];
    sentenceText?: string;
    sentenceTextIsDirty?: boolean;
    sentenceTransformations?: ITextTransformationsMap;
    sentenceTranslations?: ISentenceTranslationReducerState[];
}
