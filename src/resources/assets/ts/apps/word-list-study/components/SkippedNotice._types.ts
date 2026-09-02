import type { IFlashcardSkipped } from '@root/connectors/backend/IWordListApi';

export interface IProps {
  numberOfRequested: number;
  numberOfDealt: number;
  skipped: IFlashcardSkipped[];
}
