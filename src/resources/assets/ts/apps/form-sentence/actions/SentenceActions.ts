import {
    ReduxThunkDispatch,
} from '@root/_types';
import { ParagraphState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';
import convertTransformationToTextComponents from '@root/apps/sentence-inspector/utilities/TextConverter';
import { DI, resolve } from '@root/di';
import { setValidationErrors } from '@root/components/Form/Validation';
import { ISentenceFragmentEntity, SentenceFragmentType } from '@root/connectors/backend/IBookApi';
import IContributionResourceApi, { ISaveSentenceContributionEntity } from '@root/connectors/backend/IContributionResourceApi';
import ILanguageApi from '@root/connectors/backend/ILanguageApi';
import ValidationError from '@root/connectors/ValidationError';

import { RootReducer } from '../reducers';
import { ISentenceTranslationReducerState } from '../reducers/child-reducers/SentenceTranslationReducer._types';
import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';
import { ISentenceTranslationsReducerState } from '../reducers/SentenceTranslationsReducer._types';
import { TextTransformationsReducerState } from '../reducers/TextTransformationsReducer._types';
import { parseFragments, mergeFragments } from '../utilities/fragments';
import { convertParagraphsToString, convertTextComponentsToParagraphs, convertTextComponentsToString } from '../utilities/transformations';
import { rebuildTranslations } from '../utilities/translations';

import Actions from './Actions';

export default class GlossActions {
    constructor(
        private _contributionApi: IContributionResourceApi = resolve(DI.ContributionApi),
        private _languageApi: ILanguageApi = resolve(DI.LanguageApi)) {}

    /**
     * [Preloaded] Updates the model according to the preloaded state.
     * @param sentence model state.
     */
    public setLoadedSentence(sentence: ISentenceReducerState) {
        return {
            sentence,
            type: Actions.ReceiveSentence,
        };
    }

    /**
     * [Preloaded] Updates the model according to the preloaded state. Must be called after `setLoadedSentence`.
     * @param sentenceFragments model state.
     */
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

    /**
     * [Preloaded] Updates the model according to the preloaded state. Must be called after `setLoadedSentenceFragments`.
     * @param textTransformations model state.
     */
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

    /**
     * [Preloaded] Updates the model according to the preloaded state. Must be called after `setLoadedTransformations`.
     * @param sentenceTranslations model state.
     */
    public setLoadedSentenceTranslations(sentenceTranslations: ISentenceTranslationsReducerState) {
        return {
            sentenceTranslations,
            type: Actions.ReceiveTranslation,
        };
    }

    /**
     * Updates the specified field on the corresponding metadata field to the specified value.
     * @param field field on `ISentenceReducerState`.
     * @param value value for the specified field.
     */
    public setMetadataField<T extends keyof ISentenceReducerState>(field: T, value: ISentenceReducerState[T]) {
        return {
            field,
            type: Actions.SetField,
            value,
        };
    }

    /**
     * Updates a fragment's field to the given value.
     * @param sentenceFragment the fragment to update.
     * @param field a field on the fragment to update.
     * @param value the value to update the field to.
     */
    public setFragmentField<T extends keyof ISentenceFragmentEntity>(sentenceFragment: ISentenceFragmentEntity,
        field: T, value: ISentenceFragmentEntity[T]) {
        return {
            field,
            sentenceFragment,
            type: Actions.SetFragmentField,
            value,
        };
    }

    /**
     * Updates the latin transcription with the selected texts. Paragraphs are optional but recommended.
     * @param text new latin transcription of current fragments.
     * @param paragraphs paragraphs that corresponds with the specified text body.
     * @param dirty whether the input is considered changed from original, preloaded state.
     */
    public setLatinText(text: string, paragraphs: ParagraphState[] = [], dirty = true) {
        return {
            dirty,
            paragraphs,
            latinText: text,
            type: Actions.SetLatinText,
        };
    }

    /**
     * Updates the translation for a specified paragraph and sentence.
     * @param translation the translation.
     */
    public setTranslation(translation: ISentenceTranslationReducerState) {
        return {
            sentenceTranslation: translation,
            type: Actions.SetTranslation,
        };
    }

    /**
     * Transforms the specified fragments into a latin transcription, and updates the model with the result.
     * @param fragments fragments.
     */
    public setTextWithLatinTransformer(fragments: ISentenceFragmentsReducerState) {
        return (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            const transformer = getState().textTransformations.latin;
            if (transformer) {
                const text = convertTransformationToTextComponents(null, transformer, fragments);
                const textString = convertTextComponentsToString(text, fragments);
                dispatch(this.setLatinText(textString, text.paragraphs, false));
            }
        };
    }

    /**
     * Parses the given string into fragments and updates all related models appropriately at the same time.
     * @param text new text.
     */
    public reloadFragments(text: string) {
        return async (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            const oldFragments = getState().sentenceFragments;
            const oldTranslations = getState().sentenceTranslations;
            const languageId   = getState().sentence.languageId;

            const language = await this._languageApi.find(languageId, 'id');
            if (language === null) {
                // oof bad stuff!
                throw new Error(`Failed to parse fragments because language ${languageId} does not exist!`);
            }

            // Re-create fragments based on the next text body. These fragments should be merged with
            // existing fragments (i.e. fragments prior to the change) to ensure that only state is
            // reset for fragments affected by the forward change.
            const newFragments = await parseFragments(text, language.tengwarMode || null);
            mergeFragments(newFragments, oldFragments);

            // Validate the transformation and rely on the server to render the necessary transformations.
            // A transformation is a writing system transformation of the specified sentence. A transformation
            // is the ordering of each fragment while rendering sentences. For example, a sentence written
            // using a Latin transformation would differ from the Chinese transformation.
            const api = this._contributionApi;
            const metadata = await api.validateTransformations(newFragments, languageId);

            // Create a string using the Latin transformation. Regardless of localization, a Latin transformation
            // is always expected from the server side.
            const textComponents = convertTransformationToTextComponents(null, metadata.transformations.latin, newFragments);
            const latinText = convertTextComponentsToString(textComponents, newFragments);

            // Carry over the old translations into the new array of translations. Only the paragraphs and
            // sentences that have changed since the last update will be blanked out. All else should be
            // as it was prior to the reconstruction.
            const translations = rebuildTranslations(oldTranslations, oldFragments, newFragments);

            // Use suggestions for fragments that have not been assigned a gloss.
            newFragments.forEach((f) => {
                if (f.type !== SentenceFragmentType.Word || f.glossId) {
                    return;
                }

                const fragment = f.fragment.toLocaleLowerCase();
                const suggestion = metadata.suggestions[fragment];
                if (suggestion !== undefined) {
                    f.glossId = suggestion.glossId;
                    f.speechId = suggestion.speechId;
                    f.glossInflections = suggestion.inflectionIds.map((inflectionId, i) => ({
                        inflectionId,
                        order: i,
                    }));
                }
            });

            dispatch({
                dirty: false,
                latinText,
                paragraphs: textComponents.paragraphs,
                type: Actions.ReloadAllFragments,
                sentenceFragments: newFragments,
                sentenceTranslations: translations,
                textTransformations: metadata.transformations,
            });
        };
    }

    public saveSentence(args: ISaveSentenceContributionEntity) {
        return async (dispatch: ReduxThunkDispatch) => {
            dispatch(setValidationErrors(null));
            try {
                const response = await this._contributionApi.saveSentence(args);
                window.location.href = response.url;
            } catch (e) {
                if (e instanceof ValidationError) {
                    dispatch(setValidationErrors(e));
                }
            }
        };
    }
}
