import type { IFlashcardOption } from '@root/connectors/backend/IWordListApi';

export interface IProps {
  option: IFlashcardOption;
  /** Disabled once the card has been flipped, so the answer cannot be changed. */
  disabled: boolean;
  /** Receives the option object itself - never a value read off the event target. */
  onSelect: (option: IFlashcardOption) => void;
}
