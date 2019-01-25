import React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
    DeepPartial,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { Actions } from './actions';
import { IProps } from './index._types';
import rootReducer, { RootReducer } from './reducers';

import Discuss from './containers/Discuss';

const Inject = (props: IProps) => {
    let initialState: DeepPartial<RootReducer>;
    if (props) {
        initialState = {
            pagination: {
                currentPage: props.currentPage,
                noOfPages: props.noOfPages,
                pages: props.pages,
            },
            posts: props.posts,
            thread: props.thread,
        };
    }

    const store = createStore(rootReducer, initialState,
        applyMiddleware(thunkMiddleware),
    );

    return <Provider store={store}>
        <Discuss />
    </Provider>;
};

export default Inject;
