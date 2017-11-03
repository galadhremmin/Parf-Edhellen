import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDSentenceReducer from './reducers';
import EDFragmentExplorer from './components/fragment-explorer';

const load = () => {
    const data = JSON.parse( document.getElementById('ed-preload-sentence-data').textContent );

    const store = createStore(EDSentenceReducer, {
            fragments: data.fragments,
            latin: data.latin,
            tengwar: data.tengwar
        },
        applyMiddleware(thunkMiddleware)
    );

    ReactDOM.render(
        <Provider store={store}>
            <div>
                <EDFragmentExplorer />
            </div>
        </Provider>,
        document.getElementById('ed-fragment-navigator')
    );
};

window.addEventListener('load', function () {
    window.setTimeout(load, 0);
});
