import { configureStore } from '@reduxjs/toolkit';
import { useEffect } from 'react';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';

import { SentenceActions } from './actions';
import { IProps } from './index._types';
import rootReducer from './reducers';

import SentenceForm from './containers/SentenceForm';

import '@root/components/AgGrid.scss';
import registerApp from '../app';
import { ISentenceTranslationEntity } from '@root/connectors/backend/IBookApi';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
 })

const Inject = (props: IProps) => {
    const {
        sentence,
        sentenceFragments,
        sentenceTransformations,
        sentenceTranslations,
        prefetched,
    } = props;

    useEffect(() => {
        const dispatch = store.dispatch as ReduxThunkDispatch;

        const actions = new SentenceActions();
        if (prefetched) {
            if (sentence !== undefined) {
                dispatch(actions.setLoadedSentence(sentence));
            }

            if (sentenceFragments !== undefined) {
                dispatch(actions.setLoadedSentenceFragments(sentenceFragments));
            }

            if (sentenceTransformations !== undefined) {
                dispatch(actions.setLoadedTransformations(sentenceTransformations));
            }

            if (sentenceTranslations !== undefined) {
                if (sentenceFragments !== undefined && //
                    sentenceFragments.length > 0 &&  //
                    sentenceTranslations.length < 1) {
                    // translations may not have been properly instantiated as a reflection of
                    // sentence fragments, so the logic below creates a unique translation row
                    // per paragraph identified from the fragments. This logic is only ever going
                    // to be relevant during form initialization and prefetch so there's no
                    // point in trying to extract and generalize this logic for usage elsewhere.
                    dispatch(
                        actions.setLoadedSentenceTranslations(
                            [...sentenceFragments.reduce((rows, f) => {
                                const key = `${f.paragraphNumber}|${f.sentenceNumber}`;
                                if (! rows.has(key)) {
                                    rows.set(key, {
                                        paragraphNumber: f.paragraphNumber,
                                        sentenceNumber: f.sentenceNumber,
                                        translation: '',
                                    });
                                }
        
                                return rows;
                            }, new Map<string, ISentenceTranslationEntity>()) //
                            .values()]
                        ),
                    );
                } else {
                    dispatch(actions.setLoadedSentenceTranslations(sentenceTranslations));
                }
            }
        }
    }, []);

    return <Provider store={store}>
        <SentenceForm />
    </Provider>;
};

export default registerApp(Inject);
