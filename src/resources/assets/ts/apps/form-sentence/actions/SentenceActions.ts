import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import { DI, resolve } from '@root/di';
import { ITextTransformation, ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import IContributionResourceApi from '@root/connectors/backend/IContributionResourceApi';
import ILanguageApi from '@root/connectors/backend/ILanguageApi';

import { RootReducer } from '../reducers';
import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';
import { ISentenceTranslationsReducerState } from '../reducers/SentenceTranslationsReducer._types';
import { TextTransformationsReducerState } from '../reducers/TextTransformationsReducer._types';
import { convertTransformationToString } from '../utilities/transformations';

import Actions from './Actions';
import { parseFragments, mergeFragments } from '../utilities/fragments';

export default class GlossActions {
    constructor(
        // private _glossApi: IGlossResourceApi = resolve(DI.GlossApi),
        private _contributionApi: IContributionResourceApi = resolve(DI.ContributionApi),
        private _languageApi: ILanguageApi = resolve(DI.LanguageApi)) {}

    public setLoadedSentence(sentence: ISentenceReducerState) {
        return {
            sentence,
            type: Actions.ReceiveSentence,
        };
    }

    public setLoadedSentenceFragments(sentenceFragments: ISentenceFragmentsReducerState) {
        return (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            dispatch({
                sentenceFragments,
                type: Actions.ReceiveFragment,
            });

            const textTransformations = getState().textTransformations;
            if (textTransformations.latin !== undefined) {
                dispatch(this.setTextWithLatinTransformer(sentenceFragments));
            }
        };
    }

    public setLoadedTransformations(textTransformations: TextTransformationsReducerState) {
        return (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            dispatch({
                textTransformations,
                type: Actions.ReceiveTransformation,
            });

            if (textTransformations.latin !== undefined) {
                dispatch(this.setTextWithLatinTransformer(getState().sentenceFragments));
            }
        };
    }

    public setLoadedSentenceTranslations(sentenceTranslations: ISentenceTranslationsReducerState) {
        return {
            sentenceTranslations,
            type: Actions.ReceiveTranslation,
        };
    }

    public setMetadataField(field: keyof ISentenceReducerState, value: any) {
        return {
            field,
            type: Actions.SetField,
            value,
        };
    }

    public setLatinText(text: string, dirty = true) {
        return {
            dirty,
            latinText: text,
            type: Actions.SetLatinText,
        };
    }

    public setFragmentField<T extends keyof ISentenceFragmentEntity>(sentenceFragment: ISentenceFragmentEntity,
        field: T, value: ISentenceFragmentEntity[T]) {
        return {
            field,
            sentenceFragment,
            type: Actions.SetFragmentField,
            value,
        };
    }

    public setTextWithLatinTransformer(fragments: ISentenceFragmentsReducerState) {
        return (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            const transformer = getState().textTransformations.latin;
            if (transformer) {
                const text = convertTransformationToString(transformer, fragments);
                dispatch(this.setLatinText(text, false));
            }
        };
    }

    public reloadFragments(text: string) {
        return async (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            const oldFragments = getState().sentenceFragments;
            const languageId   = getState().sentence.languageId;

            const language = this._languageApi.find(languageId, 'id');
            if (language === null) {
                // oof bad stuff!
                throw new Error(`Failed to parse fragments because language ${languageId} does not exist!`);
            }

            const newFragments = await parseFragments(text);
            mergeFragments(newFragments, oldFragments);

            // TODO -- update everything :)
        }
    }
}
