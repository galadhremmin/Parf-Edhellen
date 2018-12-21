import { IGlossEntity } from './BookApiConnector._types';

export interface ICardRequest {
    id: number;
    not: number[];
}

export interface ICardResponse {
    options: string[];
    translationId: number;
    word: string;
}

export interface ICardTestRequest {
    flashcardId: number;
    translation: string;
    translationId: number;
}

export interface ICardTestResponse {
    correct: boolean;
    gloss: IGlossEntity;
}
