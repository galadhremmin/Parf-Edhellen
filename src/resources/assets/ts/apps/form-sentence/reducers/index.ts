import { combineReducers } from 'redux';

import type { CreateRootReducer } from '@root/_types';
import { ValidationErrorReducer as errors } from '@root/components/Form/Validation';
import { default as latinText } from './LatinTextReducer';
import { default as sentenceFragmentsLoading } from './SentenceFragmentsLoadingReducer';
import { default as sentenceFragments } from './SentenceFragmentsReducer';
import { default as sentence } from './SentenceReducer';
import { default as sentenceTranslations } from './SentenceTranslationsReducer';
import { default as textTransformations } from './TextTransformationsReducer';

const reducers = {
    errors,
    latinText,
    sentence,
    sentenceFragments,
    sentenceFragmentsLoading,
    sentenceTranslations,
    textTransformations,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
