import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDConfig from 'ed-config';
import { saveState, loadState } from 'ed-session-storage-state';
import EDGlossAdminReducer from './reducers/admin';
import EDGlossForm from './components/forms';

const load = () => {
    let preloadedState = undefined;

    const glossDataContainer = document.getElementById('ed-preloaded-gloss');
    if (glossDataContainer) {
        const glossData = JSON.parse(glossDataContainer.textContent);

        preloadedState = {
            ...glossData,
            languages: EDConfig.languages()    
        };
    }

    const store = createStore(EDGlossAdminReducer, preloadedState,
        applyMiddleware(thunkMiddleware)
    );

    const container = document.getElementById('ed-gloss-form');
    const admin = /true/i.test(container.dataset['admin'] || 'true');
    const confirmButtonText = container.dataset['confirmButtonText'] || undefined;

    ReactDOM.render(
        <Provider store={store}>
            <EDGlossForm admin={admin} confirmButtonText={confirmButtonText} />
        </Provider>,
        container
    );
};

window.addEventListener('load', function () {
    window.setTimeout(load, 0);
});
