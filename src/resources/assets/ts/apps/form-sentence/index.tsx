import { configureStore } from '@reduxjs/toolkit';
import { useEffect } from 'react';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';

import { SentenceActions } from './actions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import SentenceForm from './containers/SentenceForm';

import '@root/components/AgGrid.scss';
import registerApp from '../app';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
 })

const Inject = (props: IProps) => {
    const {
        sentence,
        sentenceFragments,
        sentenceTransformations,
        sentenceTranslations,
        prefetched,
    } = props;

    useEffect(() => {
        const dispatch = store.dispatch as ReduxThunkDispatch;

        const actions = new SentenceActions();
        if (prefetched) {
            if (sentence !== undefined) {
                dispatch(actions.setLoadedSentence(sentence));
            }

            if (sentenceFragments !== undefined) {
                dispatch(actions.setLoadedSentenceFragments(sentenceFragments));
            }

            if (sentenceTransformations !== undefined) {
                dispatch(actions.setLoadedTransformations(sentenceTransformations));
            }

            if (sentenceTranslations !== undefined) {
                dispatch(actions.setLoadedSentenceTranslations(sentenceTranslations));
            }
        }
    }, []);

    return <Provider store={store}>
        <SentenceForm />
    </Provider>;
};

export default registerApp(Inject);
