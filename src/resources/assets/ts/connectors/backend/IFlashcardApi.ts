import { ILexicalEntryEntity } from './IBookApi';

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
    gloss: ILexicalEntryEntity;
}

export default interface IFlashcardApi {
    card(args: ICardRequest): Promise<ICardResponse>;
    test(args: ICardTestRequest): Promise<ICardTestResponse>;
}
