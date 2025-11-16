import type { ReduxThunkDispatch } from '@root/_types';
import type { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import {
    type IGloss
} from '@root/connectors/backend/IWordFinderApi';
import { resolve } from '@root/di';

import { DI } from '@root/di/keys';
import Actions from '../actions/Actions';
import { splitWord } from '../utilities/word-splitter';
import {
    GameStage,
    type IGameAction,
} from './GameActions._types';

export default class GameActions {
    constructor(
        private _gameApi = resolve(DI.WordFinderApi),
        private _languageApi = resolve(DI.LanguageApi)) {}

    public loadGame(languageId: number) {
        return async (dispatch: ReduxThunkDispatch) => {
            const language = await this._languageApi.find(languageId, 'id');
            if (! language) {
                // the language cannot be found - quit.
                return;
            }

            const game = await this._gameApi.newGame(languageId);
            dispatch(
                this.initializeGame(game.glossary, language),
            );
        };
    }

    public initializeGame(glossary: IGloss[], language: ILanguageEntity) {
        const parts: string[] = [];
        for (const gloss of glossary) {
            splitWord(gloss.word).forEach((part) => {
                parts.push(part);
            });
        }

        return {
            type: Actions.InitializeGame,
            stage: GameStage.Running,
            glossary,
            language,
            parts,
        } as IGameAction;
    }

    public selectPart(partId: number) {
        return {
            type: Actions.SelectPart,
            selectedPartId: partId,
        } as IGameAction;
    }

    public deselectPart(partId: number) {
        return {
            type: Actions.DeselectPart,
            selectedPartId: partId,
        } as IGameAction;
    }

    public discoverWord(lexicalEntryId: number) {
        return {
            type: Actions.DiscoverWord,
            lexicalEntryId,
        };
    }

    public setTime(time: number) {
        return {
            type: Actions.SetTime,
            time,
        };
    }

    public setStage(stage: GameStage) {
        return {
            type: Actions.SetStage,
            stage,
        };
    }
}