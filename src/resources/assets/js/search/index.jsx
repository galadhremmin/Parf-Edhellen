import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import { EDSearchResultsReducer } from './reducers';
import EDSearchBar from './components/search-bar';
import EDSearchResults from './components/search-results';

const load = () => {
    const stateContainer = document.getElementById('ed-preloaded-book');
    let preloadedState = undefined;
    if (stateContainer) {
        preloadedState = {
            bookData: JSON.parse(stateContainer.textContent)
        };
    }
    
    const store = createStore(EDSearchResultsReducer, preloadedState,
        applyMiddleware(thunkMiddleware)
    );

    ReactDOM.render(
        <Provider store={store}>
            <div>
                <EDSearchBar />
                <EDSearchResults />
            </div>
        </Provider>,
        document.getElementById('ed-search-component')
    );

    // SEO: delete content specifically only present for bots
    const seoContent = document.getElementById('ed-book-for-bots');
    if (seoContent) {
        seoContent.parentNode.removeChild(seoContent);
    }
};

window.addEventListener('load', function () {
    window.setTimeout(load, 0);
});
