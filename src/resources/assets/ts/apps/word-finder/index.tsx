
import { configureStore } from '@reduxjs/toolkit';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';


import WordFinder from './containers/WordFinder';
import { IGameProps } from './index._types';
import rootReducer from './reducers';
import registerApp from '../app';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
 });

const Inject = (props: IGameProps) => {
    return <Provider store={store}>
        <WordFinder languageId={props.languageId} />
    </Provider>;
};

export default registerApp(Inject);
