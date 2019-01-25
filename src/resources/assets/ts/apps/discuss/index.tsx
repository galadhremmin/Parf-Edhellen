import React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import Discuss from './containers/Discuss';
import { IProps } from './index._types';

import rootReducer from './reducers';

const Inject = (props: IProps) => {
    const store = createStore(rootReducer, undefined,
        applyMiddleware(thunkMiddleware),
    );

    return <Provider store={store}>
        <Discuss {...props} />
    </Provider>;
};

export default Inject;
