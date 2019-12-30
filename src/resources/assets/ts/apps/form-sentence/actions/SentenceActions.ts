import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import IContributionResourceApi from '@root/connectors/backend/IContributionResourceApi';
import { DI, resolve } from '@root/di';

import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';
import { ISentenceTranslationsReducerState } from '../reducers/SentenceTranslationsReducer._types';

import Actions from './Actions';

export default class GlossActions {
    constructor(
        // private _glossApi: IGlossResourceApi = resolve(DI.GlossApi),
        private _contributionApi: IContributionResourceApi = resolve(DI.ContributionApi)) {}

    public setSentence(sentence: ISentenceReducerState) {
        return {
            sentence,
            type: Actions.ReceiveSentence,
        };
    }

    public setSentenceFragments(sentenceFragments: ISentenceFragmentsReducerState) {
        return {
            sentenceFragments,
            type: Actions.ReceiveFragment,
        };
    }

    public setSentenceTranslations(sentenceTranslations: ISentenceTranslationsReducerState) {
        return {
            sentenceTranslations,
            type: Actions.ReceiveTranslation,
        };
    }
}
