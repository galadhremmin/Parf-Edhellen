import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDSentenceReducer from './reducers';
import EDFragmentExplorer from './components/fragment-explorer';

const store = createStore(EDSentenceReducer, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

window.addEventListener('load', function () {
    ReactDOM.render(
        <Provider store={store}>
            <EDFragmentExplorer />
        </Provider>,
        document.getElementById('ed-fragment-navigator')
    );
});
