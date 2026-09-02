import type { IFlashcardDeck } from '@root/connectors/backend/IWordListApi';

export interface IMissedCard {
  lexicalEntryId: number;
  word: string;
  /** The translation that would have been right. */
  expected: string;
  actual: string;
  url: string;
}

export interface IProps {
  deck: IFlashcardDeck;
  numberOfCorrect: number;
  numberOfWrong: number;
  missed: IMissedCard[];
  onRetryMissed: () => void;
  onStudyAgain: () => void;
}
