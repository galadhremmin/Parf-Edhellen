import { ReduxThunkDispatch } from '@root/_types';
import {
    IGloss,
    IWordFinderApi,
} from '@root/connectors/backend/IWordFinderApi';
import { resolve, DI } from '@root/di';

import { splitWord } from '../utilities/word-splitter';
import { Actions } from '../actions';
import { RootReducer } from '../reducers';
import {
    IGameAction,
    GameStage,
} from './GameActions._types';

export default class GameActions {
    constructor(private _api = resolve<IWordFinderApi>(DI.WordFinderApi)) {}

    public loadGame(languageId: number) {
        return async (dispatch: ReduxThunkDispatch) => {
            const game = await this._api.newGame(languageId);
            dispatch(
                this.initializeGame(game.glossary),
            );
        };
    }

    public initializeGame(glossary: IGloss[]) {
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