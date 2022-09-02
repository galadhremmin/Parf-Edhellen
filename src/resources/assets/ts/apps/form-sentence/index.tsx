import React, { useEffect } from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';
import { composeEnhancers } from '@root/utilities/func/redux-tools';

import { SentenceActions } from './actions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import SentenceForm from './containers/SentenceForm';

import '@ag-grid-community/core/dist/styles/ag-grid.css';
import '@ag-grid-community/core/dist/styles/ag-theme-balham.css';

const store = createStore(rootReducer, undefined,
    composeEnhancers('form-sentence')(
        applyMiddleware(thunkMiddleware),
    ),
);

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

export default Inject;
