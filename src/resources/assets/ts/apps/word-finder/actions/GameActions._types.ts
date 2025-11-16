import type { IReduxAction } from '@root/_types';
import type { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import type { IGloss } from '@root/connectors/backend/IWordFinderApi';

export interface IGameAction extends IReduxAction {
    lexicalEntryId?: number;
    glossary?: IGloss[];
    language?: ILanguageEntity;
    parts?: string[];
    selectedPartId?: number;
    stage?: GameStage;
    time?: number;
}

export const enum GameStage {
    Loading = 0,
    Running = 1,
    Success = 2,
}
