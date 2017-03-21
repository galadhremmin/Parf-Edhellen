import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import { EDSearchResults } from './reducers';
import EDSearchToolsApp from './components/tools-app';
import EDSearchResultApp from './components/results-app';

const store = createStore(EDSearchResults, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

ReactDOM.render(
    <Provider store={store}>
        <div>
            <EDSearchToolsApp />
            <EDSearchResultApp />
        </div>
    </Provider>,
    document.getElementById('search-component')
);
