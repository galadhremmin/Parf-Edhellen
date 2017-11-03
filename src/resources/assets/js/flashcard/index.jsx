import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDFlashcardReducer from './reducers';
import EDFlashcards from './components/flashcards';

const load = () => {
    const store = createStore(EDFlashcardReducer, undefined /* <- preloaded state */,
        applyMiddleware(thunkMiddleware)
    );

    const container = document.getElementById('ed-flashcard-component');
    const flashcardId = parseInt(container.dataset['flashcardId'], 10);
    const tengwarMode = container.dataset['languageTengwarMode'];

    ReactDOM.render(
        <Provider store={store}>
            <div>
                <EDFlashcards flashcardId={flashcardId} tengwarMode={tengwarMode} />
            </div>
        </Provider>,
        container
    );
};

window.addEventListener('load', function () {
    window.setTimeout(load, 0);
});
