import { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';
import { ICardResponse } from '@root/connectors/backend/IFlashcardApi';

export interface IProps {
    flashcardId: number;
    tengwarMode: string;
}

interface ILocalState {
    correct: boolean;
    flipped: boolean;
    lexicalEntry: ILexicalEntryEntity;
    loading: boolean;
}

export type IState = ICardResponse & ILocalState;
