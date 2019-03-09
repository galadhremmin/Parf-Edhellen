import React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { Actions } from './actions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import Discuss from './containers/Discuss';

const Inject = (props: IProps) => {
    const store = createStore(rootReducer, undefined,
        applyMiddleware(thunkMiddleware),
    );
    if (props.thread !== undefined) {
        store.dispatch({
            threadData: props,
            type: Actions.ReceiveThread,
        });
    }

    return <Provider store={store}>
        <Discuss />
    </Provider>;
};

export default Inject;
