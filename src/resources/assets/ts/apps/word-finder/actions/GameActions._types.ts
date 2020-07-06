import { IReduxAction } from '@root/_types';
import { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import { IGloss } from '@root/connectors/backend/IWordFinderApi';

export interface IGameAction extends IReduxAction {
    glossId?: number;
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
