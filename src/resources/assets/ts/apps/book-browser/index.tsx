import React from 'react';
import { Provider } from 'react-redux';
import thunkMiddleware from 'redux-thunk';

import {
    applyMiddleware,
    createStore,
} from 'redux';
import rootReducer from './reducers';

import Glossary from './containers/Glossary';
import Search from './containers/Search';
import SearchResults from './containers/SearchResults';

const Inject = () => {
    const store = createStore(rootReducer, undefined,
        applyMiddleware(thunkMiddleware),
    );

    return <Provider store={store}>
        <React.Fragment>
            <Search />
            <SearchResults />
            <Glossary />
        </React.Fragment>
    </Provider>;
};

export default Inject;
