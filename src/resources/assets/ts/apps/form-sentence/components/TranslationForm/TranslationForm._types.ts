import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceTranslation } from '@root/connectors/backend/IBookApi';
import { ParagraphState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';
import { ISentenceTranslationReducerState } from '../../reducers/child-reducers/SentenceTranslationReducer._types';

export interface ITranslationRow extends ISentenceTranslation {
    paragraphNumber: number;
    sentenceText: string;
}

export interface IState {
    lastParagraphsRef: ParagraphState[];
    translationRows: ITranslationRow[];
}

export interface ITranslationFormEvents {
    onTranslationChange: ComponentEventHandler<ITranslationRow>;
}

export interface IProps extends ITranslationFormEvents {
    paragraphs: ParagraphState[];
    translations: ISentenceTranslationReducerState[];
}
