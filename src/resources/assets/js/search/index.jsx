import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import { EDSearchResultsReducer } from './reducers';
import EDSearchBar from './components/search-bar';
import EDSearchResults from './components/search-results';

const store = createStore(EDSearchResultsReducer, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

window.addEventListener('load', function () {
    enableSmoothScrolling();

    ReactDOM.render(
        <Provider store={store}>
            <div>
                <EDSearchBar />
                <EDSearchResults />
            </div>
        </Provider>,
        document.getElementById('ed-search-component')
    );
});
