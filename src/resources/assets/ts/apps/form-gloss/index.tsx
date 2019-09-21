import React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';
import { composeEnhancers } from '@root/utilities/func/redux-tools';

import GlossActions from './actions/GlossActions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import GlossForm from './containers/GlossForm';

const Inject = (props: IProps) => {
    const store = createStore(rootReducer, undefined,
        composeEnhancers(
            applyMiddleware(thunkMiddleware),
        ),
    );

    const {
        confirmButton,
        gloss,
        prefetched,
    } = props;

    const dispatch = store.dispatch as ReduxThunkDispatch;

    const actions = new GlossActions();
    if (prefetched) {
        if (gloss !== undefined) {
            dispatch(actions.setGloss(gloss));
        }
    }

    return <Provider store={store}>
        <GlossForm confirmButton={confirmButton || null} />
    </Provider>;
};

Inject.defaultProps = {
    prefetched: true,
} as Partial<IProps>;

export default Inject;
