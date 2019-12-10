import React from 'react';
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

const Inject = (props: IProps) => {
    const store = createStore(rootReducer, undefined,
        composeEnhancers(
            applyMiddleware(thunkMiddleware),
        ),
    );

    const {
        sentence,
        sentenceFragments,
        sentenceTranslations,
        prefetched,
    } = props;

    const dispatch = store.dispatch as ReduxThunkDispatch;

    const actions = new SentenceActions();
    if (prefetched) {
        if (sentence !== undefined) {
            dispatch(actions.setSentence(sentence));
            dispatch(actions.setSentenceFragments(sentenceFragments));
        }
    }

    return <Provider store={store}>
        <SentenceForm />
    </Provider>;
};

export default Inject;
