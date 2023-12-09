import { useEffect } from 'react';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';
import { configureStore } from '@reduxjs/toolkit';

import { ReduxThunkDispatch } from '@root/_types';
import { composeEnhancers } from '@root/utilities/func/redux-tools';

import GlossActions from './actions/GlossActions';
import { FormSection, IProps } from './index._types';
import rootReducer from './reducers';

import Form from './containers';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
    enhancers: (getDefaultEnhancers) => getDefaultEnhancers().concat(composeEnhancers('form-gloss')),
 })

const Inject = (props: IProps) => {
    const {
        confirmButton,
        gloss,
        formSections,
        inflections,
        prefetched,
    } = props;

    useEffect(() => {
        const dispatch = store.dispatch as ReduxThunkDispatch;

        const actions = new GlossActions();
        if (prefetched) {
            if (gloss !== undefined) {
                dispatch(actions.setLoadedGloss(gloss));
            }
            if (inflections !== undefined) {
                dispatch(actions.setLoadedInflections(inflections));
            }
        }
    }, []);

    return <Provider store={store}>
        <Form confirmButton={confirmButton || undefined} formSections={formSections} />
    </Provider>;
};

Inject.defaultProps = {
    prefetched: true,
    formSections: [ FormSection.Gloss, FormSection.Inflections ]
} as Partial<IProps>;

export default Inject;
