import {
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';

export interface IFragmentsReducerState {
    comments: string;
    fragment: string;
    glossId: number;
    id: number;
    inflections: IFragmentInflection[];
    nextFragmentId: number;
    previousFragmentId: number;
    sentenceNumber: number;
    speechId: number;
    speech: string;
    type: SentenceFragmentType;
}

export interface IFragmentInflection {
    inflectionId: number;
    name: string;
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
