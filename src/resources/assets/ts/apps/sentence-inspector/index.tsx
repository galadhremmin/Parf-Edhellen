import React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { SentenceActions } from './actions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import SentenceInspector from './containers/SentenceInspector';

const Inject = (props: IProps) => {
    const store = createStore(rootReducer, undefined,
        applyMiddleware(thunkMiddleware),
    );

    if (props.sentence) {
        const actions = new SentenceActions();
        store.dispatch(actions.setSentence(props.sentence));

        const matches = /^#!([0-9]+)\/([0-9]+)$/.exec(window.location.hash);
        if (matches) {
            store.dispatch(actions.selectFragment({
                id: parseInt(matches[2], 10),
                sentenceNumber: parseInt(matches[1], 10),
            }));
        }
    }

    return <Provider store={store}>
        <SentenceInspector />
    </Provider>;
};

export default Inject;
