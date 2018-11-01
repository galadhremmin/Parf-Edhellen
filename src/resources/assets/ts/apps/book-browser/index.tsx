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

/*
const stateContainer = document.getElementById('ed-preloaded-book');
let preloadedState = undefined;
if (stateContainer) {
    preloadedState = {
        bookData: JSON.parse(stateContainer.textContent)
    };
}
*/

const store = createStore(rootReducer, undefined,
    applyMiddleware(thunkMiddleware),
);

const app = <Provider store={store}>
    <React.Fragment>
        <Search />
        <SearchResults />
        <Glossary />
    </React.Fragment>
</Provider>;

// SEO: delete content specifically only present for bots
/*
const seoContent = document.getElementById('ed-book-for-bots');
if (seoContent) {
    seoContent.parentNode.removeChild(seoContent);
}
*/

export default app;
