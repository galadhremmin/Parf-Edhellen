import React, { useEffect } from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { composeEnhancers } from '@root/utilities/func/redux-tools';

import { SentenceActions } from './actions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import SentenceInspector from './containers/SentenceInspector';

const store = createStore(rootReducer, undefined,
    composeEnhancers('sentence-inspector')(applyMiddleware(thunkMiddleware)),
);

const Inject = (props: IProps) => {

    useEffect(() => {
        if (props.sentence) {
            const actions = new SentenceActions();
            store.dispatch(actions.setSentence(props.sentence));

            const matches = /^#!([0-9]+)\/([0-9]+)$/.exec(window.location.hash);
            if (matches) {
                const fragmentId = parseInt(matches[2], 10);
                const fragment = store.getState().fragments.find((f) => f.id === fragmentId);
                if (fragment) {
                    store.dispatch(actions.selectFragment(fragment));
                }
            }
        }
    }, []);

    return <Provider store={store}>
        <SentenceInspector />
    </Provider>;
};

export default Inject;
