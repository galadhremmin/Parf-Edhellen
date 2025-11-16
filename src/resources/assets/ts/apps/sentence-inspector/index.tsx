import { configureStore } from '@reduxjs/toolkit';
import { useEffect } from 'react';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';


import { SentenceActions } from './actions';
import type { IProps } from './index._types';
import rootReducer from './reducers';

import SentenceInspector from './containers/SentenceInspector';
import registerApp from '../app';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
 });

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

export default registerApp(Inject);
