import {
    IGlossInflection,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';

export interface IFragmentsReducerState {
    comments: string;
    fragment: string;
    glossId: number;
    id: number;
    glossInflections: IGlossInflection[];
    nextFragmentId: number;
    previousFragmentId: number;
    sentenceNumber: number;
    speechId: number;
    speech: string;
    tengwar: string;
    type: SentenceFragmentType;
}

export type FragmentsReducerState = IFragmentsReducerState[];

export interface IFragmentInSentenceState {
    id: number;
    fragment: string;
    sentenceNumber: number;
}

export type ParagraphState = IFragmentInSentenceState[];
export interface ITextState {
    paragraphs: ParagraphState[];
    transformerName: string;
}
