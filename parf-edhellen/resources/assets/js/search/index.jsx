import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import { EDSearchResultsReducer } from './reducers';
import EDSearchBar from './components/search-bar';
import EDSearchResults from './components/search-results';

const store = createStore(EDSearchResultsReducer, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

window.addEventListener('load', function () {
    ReactDOM.render(
        <Provider store={store}>
            <div>
                <EDSearchBar />
                <EDSearchResults />
            </div>
        </Provider>,
        document.getElementById('search-component')
    );
});
