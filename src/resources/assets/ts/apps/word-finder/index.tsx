
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { composeEnhancers } from '@root/utilities/func/redux-tools';

import WordFinder from './containers/WordFinder';
import { IGameProps } from './index._types';
import rootReducer from './reducers';

const store = createStore(rootReducer, undefined,
    composeEnhancers('word-finder')(applyMiddleware(thunkMiddleware)),
);

const Inject = (props: IGameProps) => {
    return <Provider store={store}>
        <WordFinder languageId={props.languageId} />
    </Provider>;
};

export default Inject;
