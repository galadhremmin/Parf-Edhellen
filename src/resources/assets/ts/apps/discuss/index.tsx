import React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';
import DiscussActions from './actions/DiscussActions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import Discuss from './containers/Discuss';

const Inject = (props: IProps) => {
    const store = createStore(rootReducer, undefined,
        applyMiddleware(thunkMiddleware),
    );
    if (props.thread !== undefined) {
        const actions = new DiscussActions();
        const args: any = {
            ...props,
        };
        (store.dispatch as ReduxThunkDispatch)(actions.setThread(args));
    }

    return <Provider store={store}>
        <Discuss />
    </Provider>;
};

export default Inject;
