import React, { useEffect } from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { composeEnhancers } from '@root/utilities/func/redux-tools';

import rootReducer from './reducers';
import Entities from './containers/Entities';
import Search from './containers/Search';
import SearchResults from './containers/SearchResults';

const store = createStore(rootReducer, undefined,
    composeEnhancers('book-browser')(applyMiddleware(thunkMiddleware)),
);

const Inject = () => {
    return <Provider store={store}>
        <>
            <Search />
            <SearchResults />
            <Entities />
        </>
    </Provider>;
};

export default Inject;
