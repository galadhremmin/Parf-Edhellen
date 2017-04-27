import React from 'react';
import ReactDOM from 'react-dom';
import { MemoryRouter as Router, Route } from 'react-router-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDConfig from 'ed-config';
import EDSentenceAdminReducer from './reducers/admin';
import { saveState, loadState } from 'ed-session-storage-state';
import EDSentenceForm from './components/forms/sentence-form';
import EDFragmentForm from './components/forms/fragment-form';

window.addEventListener('load', function () {
    const sentenceDataContainer = document.getElementById('ed-preloaded-sentence');
    const fragmentDataContainer = document.getElementById('ed-preloaded-sentence-fragments');

    let preloadedState = undefined;
    let creating = false;
    if (sentenceDataContainer && fragmentDataContainer) {
        const sentenceData = JSON.parse(sentenceDataContainer.textContent);
        const fragmentData = JSON.parse(fragmentDataContainer.textContent);

        preloadedState = {
            ...sentenceData,
            fragments: fragmentData,
            languages: EDConfig.languages()
        };
    } else {
        preloadedState = loadState('sentence');
        creating = true;
    }

    const store = createStore(EDSentenceAdminReducer, preloadedState,
        applyMiddleware(thunkMiddleware)
    );

    if (creating) {
        store.subscribe(() => {
            saveState('sentence', store.getState());
        });
    }

    ReactDOM.render(
        <Provider store={store}>
            <Router initialEntries={['/form', '/fragments']} initialIndex={0}>
                <div>
                    <Route path="/form" component={EDSentenceForm} />
                    <Route path="/fragments" component={EDFragmentForm} />
                </div>
            </Router>
        </Provider>,
        document.getElementById('ed-sentence-form')
    );
});


