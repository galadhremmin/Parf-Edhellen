import { ICardResponse } from '@root/connectors/backend/FlashcardApiConnector._types';
import { IGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IProps {
    flashcardId: number;
    tengwarMode: string;
}

interface ILocalState {
    correct: boolean;
    flipped: boolean;
    gloss: IGlossEntity;
    loading: boolean;
}

export type IState = ICardResponse & ILocalState;
