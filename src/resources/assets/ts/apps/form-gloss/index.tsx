import { configureStore } from '@reduxjs/toolkit';
import { useEffect } from 'react';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';

import GlossActions from './actions/GlossActions';
import { FormSection, IProps } from './index._types';
import rootReducer from './reducers';

import Form from './containers';
import registerApp from '../app';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
 })

const Inject = (props: IProps) => {
    const {
        confirmButton,
        gloss,
        formSections = [ FormSection.Gloss, FormSection.Inflections ],
        inflections,
        prefetched = true,
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

export default registerApp(Inject);
