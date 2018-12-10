import {
    SentenceFragmentType,
} from '@root/connectors/backend/BookApiConnector._types';

export interface IFragmentsReducerState {
    comments: string;
    fragment: string;
    glossId: number;
    id: number;
    nextFragmentId: number;
    previousFragmentId: number;
    sentenceNumber: number;
    speechId: number;
    speech: string;
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
