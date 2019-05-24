import { IBookGlossEntity } from '@root/connectors/backend/BookApiConnector._types';
import { ICardResponse } from '@root/connectors/backend/FlashcardApiConnector._types';

export interface IProps {
    flashcardId: number;
    tengwarMode: string;
}

interface ILocalState {
    correct: boolean;
    flipped: boolean;
    gloss: IBookGlossEntity;
    loading: boolean;
}

export type IState = ICardResponse & ILocalState;
