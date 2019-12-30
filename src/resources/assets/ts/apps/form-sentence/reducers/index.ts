import { combineReducers } from 'redux';

import { CreateRootReducer } from '@root/_types';
import { ValidationErrorReducer as errors } from '@root/components/Form/Validation';
import { default as sentenceFragments } from './SentenceFragmentsReducer';
import { default as sentence } from './SentenceReducer';
import { default as sentenceTranslations } from './SentenceTranslationsReducer';

const reducers = {
    errors,
    sentence,
    sentenceFragments,
    sentenceTranslations,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
