import { configureStore } from '@reduxjs/toolkit';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';

import CrosswordGame from './containers/CrosswordGame';
import type { ICrosswordProps } from './index._types';
import rootReducer from './reducers';
import registerApp from '../app';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
});

const Inject = (props: ICrosswordProps) => (
    <Provider store={store}>
        <CrosswordGame
            languageId={props.languageId}
            date={props.date}
            initialState={props.initialState}
        />
    </Provider>
);

export default registerApp(Inject);
