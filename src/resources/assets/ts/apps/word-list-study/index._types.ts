import type { FlashcardDirection, IWordListApi } from '@root/connectors/backend/IWordListApi';

export interface IProps {
  wordListId: number;
  wordListName: string;
  direction: FlashcardDirection;
  /** Injected by the DI container; supplied directly by the specs. */
  api?: IWordListApi;
}
