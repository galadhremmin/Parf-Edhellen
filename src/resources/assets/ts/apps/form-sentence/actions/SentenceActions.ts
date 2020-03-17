import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import { DI, resolve } from '@root/di';
import { ITextTransformation, ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import IContributionResourceApi from '@root/connectors/backend/IContributionResourceApi';

import { RootReducer } from '../reducers';
import { ISentenceFragmentsReducerState } from '../reducers/SentenceFragmentsReducer._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';
import { ISentenceTranslationsReducerState } from '../reducers/SentenceTranslationsReducer._types';
import { TextTransformationsReducerState } from '../reducers/TextTransformationsReducer._types';
import { convertTransformationToString } from '../utilities/transformations';

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
        return (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            dispatch({
                sentenceFragments,
                type: Actions.ReceiveFragment,
            });

            const textTransformations = getState().textTransformations;
            if (textTransformations.latin !== undefined) {
                dispatch(this.setTextWithLatinTransformer(textTransformations.latin,
                    sentenceFragments));
            }
        };
    }

    public setTransformations(textTransformations: TextTransformationsReducerState) {
        return (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            dispatch({
                textTransformations,
                type: Actions.ReceiveTransformation,
            });

            if (textTransformations.latin !== undefined) {
                dispatch(this.setTextWithLatinTransformer(textTransformations.latin,
                    getState().sentenceFragments));
            }
        };
    }

    public setSentenceTranslations(sentenceTranslations: ISentenceTranslationsReducerState) {
        return {
            sentenceTranslations,
            type: Actions.ReceiveTranslation,
        };
    }

    public setField(field: keyof ISentenceReducerState, value: any) {
        return {
            field,
            type: Actions.SetField,
            value,
        };
    }

    public setText(text: string) {
        return {
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

    public setTextWithLatinTransformer(transformer: ITextTransformation, fragments: ISentenceFragmentsReducerState) {
        const text = convertTransformationToString(transformer, fragments);
        return this.setText(text);
    }
}
