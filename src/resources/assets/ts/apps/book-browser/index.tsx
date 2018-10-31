import React from 'react';
import { Provider } from 'react-redux';
import thunkMiddleware from 'redux-thunk';

import {
    applyMiddleware,
    createStore,
} from 'redux';
import rootReducer from './reducers';

import GlossaryContainer from './components/GlossaryContainer';
import SearchContainer from './components/SearchContainer';
import SearchResultsContainer from './components/SearchResultsContainer';

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
        <SearchContainer />
        <SearchResultsContainer />
        <GlossaryContainer />
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
