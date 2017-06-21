import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import thunkMiddleware from 'redux-thunk';
import EDFlashcardReducer from './reducers';
import EDFlashcards from './components/flashcards';

const store = createStore(EDFlashcardReducer, undefined /* <- preloaded state */,
    applyMiddleware(thunkMiddleware)
);

window.addEventListener('load', function () {
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
});
