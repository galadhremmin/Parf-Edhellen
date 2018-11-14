import {
    SentenceFragmentType,
} from '@root/connectors/backend/BookApiConnector._types';

export interface IFragmentsReducerState {
    comments: string;
    glossId: number;
    id: number;
    sentenceNumber: number;
    speechId: number;
    speech: string;
    type: SentenceFragmentType;
}

export interface ILocalizedFragmentsReducerState {
    id: number;
    fragment: string;
    sentenceNumber: number;
}

export type FragmentsReducerState = IFragmentsReducerState[];
export type LocalizedFragmentsReducerState = ILocalizedFragmentsReducerState[];
