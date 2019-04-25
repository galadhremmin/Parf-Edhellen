import React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';
import { composeEnhancers } from '@root/utilities/func/redux-tools';

import DiscussActions from './actions/DiscussActions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import Discuss from './containers/Discuss';

const Inject = (props: IProps) => {
    const store = createStore(rootReducer, undefined,
        composeEnhancers(
            applyMiddleware(thunkMiddleware),
        ),
    );

    const {
        entityId,
        entityType,
        prefetched,
        thread,
    } = props;

    const dispatch = store.dispatch as ReduxThunkDispatch;

    const actions = new DiscussActions();
    if (prefetched) {
        if (thread !== undefined) {
            const args: any = {
                ...props,
            };
            dispatch(actions.setThread(args));
        }
    } else {
        dispatch(actions.thread({
            entityId,
            entityType,
        }));
    }

    return <Provider store={store}>
        <Discuss />
    </Provider>;
};

Inject.defaultProps = {
    prefetched: true,
} as Partial<IProps>;

export default Inject;
