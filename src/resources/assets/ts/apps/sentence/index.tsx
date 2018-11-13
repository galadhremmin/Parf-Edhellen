import React from 'react';
import { Provider } from 'react-redux';
import thunkMiddleware from 'redux-thunk';

import {
    applyMiddleware,
    createStore,
} from 'redux';
import rootReducer from './reducers';

const Inject = () => {
    const store = createStore(rootReducer, undefined,
        applyMiddleware(thunkMiddleware),
    );
    
    return <Provider store={store}>
        
    </Provider>;
};

export default Inject;
