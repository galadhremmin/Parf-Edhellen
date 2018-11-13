import React from 'react';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';
import { Provider } from 'react-redux';

import { SentenceActions } from './actions';
import rootReducer from './reducers';

const Inject = (props: any) => {
    const store = createStore(rootReducer, undefined,
        applyMiddleware(thunkMiddleware),
    );

    if (props.sentence) {
        const actions = new SentenceActions();
        store.dispatch(actions.setSentence(props.sentence));
    }

    return <Provider store={store}>
        <pre>test</pre>
    </Provider>;
};

export default Inject;
