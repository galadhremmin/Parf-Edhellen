import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router, Route } from 'react-router-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDSentenceAdminReducer from './reducers/admin';
import EDSentenceForm from './components/sentence-form';

const store = createStore(EDSentenceAdminReducer, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

window.addEventListener('load', function () {
    ReactDOM.render(
        <Provider store={store}>
            <Router>
                <Route path="/" component={EDSentenceForm} />
            </Router>
        </Provider>,
        document.getElementById('ed-sentence-form')
    );
});


