import { ReduxThunkDispatch } from '@root/_types';
import { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import ILanguageApi from '@root/connectors/backend/ILanguageApi';
import {
    IGloss,
    IWordFinderApi,
} from '@root/connectors/backend/IWordFinderApi';
import { resolve, DI } from '@root/di';

import { splitWord } from '../utilities/word-splitter';
import { Actions } from '../actions';
import {
    IGameAction,
    GameStage,
} from './GameActions._types';

export default class GameActions {
    constructor(
        private _gameApi = resolve<IWordFinderApi>(DI.WordFinderApi),
        private _languageApi = resolve<ILanguageApi>(DI.LanguageApi)) {}

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

    public discoverWord(glossId: number) {
        return {
            type: Actions.DiscoverWord,
            glossId,
        };
    }
}