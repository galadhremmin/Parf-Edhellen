
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import { thunk } from 'redux-thunk';

import { composeEnhancers } from '@root/utilities/func/redux-tools';

import WordFinder from './containers/WordFinder';
import { IGameProps } from './index._types';
import rootReducer from './reducers';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
    enhancers: (getDefaultEnhancers) => getDefaultEnhancers().concat(composeEnhancers('word-finder')),
 });

const Inject = (props: IGameProps) => {
    return <Provider store={store}>
        <WordFinder languageId={props.languageId} />
    </Provider>;
};

export default Inject;
