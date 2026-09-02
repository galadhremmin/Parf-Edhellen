import type { IFlashcardCard, IFlashcardOption } from '@root/connectors/backend/IWordListApi';

/**
 * The answered card the back face is showing. Retained across the flip back so
 * that the outgoing card stays on screen for the whole rotation.
 */
export interface IBackFace {
  card: IFlashcardCard;
  correct: boolean;
  selectedOption: IFlashcardOption | null;
}

export interface IProps {
  card: IFlashcardCard;
  /** The only direction-aware string on the card; the container composes it. */
  instruction: string;
  flipped: boolean;
  correct: boolean;
  selectedOption: IFlashcardOption | null;
  isLastCard: boolean;
  onSelect: (option: IFlashcardOption) => void;
  onNext: () => void;
}
