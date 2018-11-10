import { IReduxAction } from '@root/_types/redux';
import {
    ISentenceResponse,
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
}

export type FragmentsReducerState = IFragmentsReducerState[];
export type LocalizedFragmentsReducerState = ILocalizedFragmentsReducerState[];

export interface IFragmentsReducerAction extends IReduxAction {
    id: number;
    sentence: ISentenceResponse;
}
