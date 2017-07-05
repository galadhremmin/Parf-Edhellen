import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDSentenceReducer from './reducers';
import EDFragmentExplorer from './components/fragment-explorer';
import EDComments from '../_shared/components/comments';

const store = createStore(EDSentenceReducer, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

window.addEventListener('load', function () {
    ReactDOM.render(
        <Provider store={store}>
            <div>
                <EDFragmentExplorer />
                <EDComments context="sentence" entityId={13} />
            </div>
        </Provider>,
        document.getElementById('ed-fragment-navigator')
    );
});
