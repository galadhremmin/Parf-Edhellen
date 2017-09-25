import React from 'react';
import ReactDOM from 'react-dom';
import { MemoryRouter as Router, Route } from 'react-router-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDConfig from 'ed-config';
import EDSentenceAdminReducer from './reducers/admin';
import { SET_IS_ADMIN } from './reducers/admin';
import { saveState, loadState } from 'ed-session-storage-state';
import EDSentenceForm from './components/forms/sentence-form';
import EDFragmentForm from './components/forms/fragment-form';
import EDPreviewForm from './components/forms/preview-form';

window.addEventListener('load', function () {
    const formContainer = document.getElementById('ed-sentence-form');
    const sentenceDataContainer = document.getElementById('ed-preloaded-sentence');
    const fragmentDataContainer = document.getElementById('ed-preloaded-sentence-fragments');
    const is_admin = /true/i.test(formContainer.dataset['admin'] || 'true');

    let preloadedState = undefined;
    let creating = false;
    if (sentenceDataContainer && fragmentDataContainer) {
        const sentenceData = JSON.parse(sentenceDataContainer.textContent);
        const fragmentData = JSON.parse(fragmentDataContainer.textContent);

        preloadedState = {
            ...sentenceData,
            fragments: fragmentData.fragments,
            latin: fragmentData.latin,
            tengwar: fragmentData.tengwar,
            languages: EDConfig.languages(),
            is_admin
        };
    }
    
    const store = createStore(EDSentenceAdminReducer, preloadedState,
        applyMiddleware(thunkMiddleware)
    );

    // An unfortunate necessity as preloadedState (which is passed as initial state)
    // completely overrides _all_ state, and it is not possible to know what the initial
    // state for the other properties will be. Therefore, dispatch a SET_IS_ADMIN command
    // to the store with the appropriate state.
    //
    // This should be carried out before the store is passed through to the Provider.
    store.dispatch({
        type: SET_IS_ADMIN,
        is_admin
    });

    ReactDOM.render(
        <Provider store={store}>
            <Router initialEntries={['/form', '/fragments', '/preview']} initialIndex={0}>
                <div>
                    <Route path="/form" component={EDSentenceForm} />
                    <Route path="/fragments" component={EDFragmentForm} />
                    <Route path="/preview" component={EDPreviewForm} />
                </div>
            </Router>
        </Provider>,
        formContainer
    );
});
