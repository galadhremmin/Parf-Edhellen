import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import { EDSearchResults } from './reducers';
import EDSearchBarApp from './components/search-bar-app';
import EDSearchResultsApp from './components/search-results-app';

const store = createStore(EDSearchResults, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

window.addEventListener('load', function () {
    ReactDOM.render(
        <Provider store={store}>
            <div>
                <EDSearchBarApp />
                <EDSearchResultsApp />
            </div>
        </Provider>,
        document.getElementById('search-component')
    );
});
