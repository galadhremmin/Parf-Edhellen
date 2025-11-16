import type { ILexicalEntryEntity } from './IBookApi';

export interface ICardRequest {
    id: number;
    not: number[];
}

export interface ICardResponse {
    options: string[];
    glossId: number;
    word: string;
}

export interface ICardTestRequest {
    flashcardId: number;
    translation: string;
    glossId: number;
}

export interface ICardTestResponse {
    correct: boolean;
    lexicalEntry: ILexicalEntryEntity;
}

export default interface IFlashcardApi {
    card(args: ICardRequest): Promise<ICardResponse>;
    test(args: ICardTestRequest): Promise<ICardTestResponse>;
}
