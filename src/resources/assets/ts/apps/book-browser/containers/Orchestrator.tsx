import { configureStore } from '@reduxjs/toolkit';
import {
    useCallback,
    useEffect,
    useRef,
} from 'react';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';

import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { withPropInjection } from '@root/di';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import { DI } from '@root/di/keys';
import { SearchActions } from '../actions';
import rootReducer from '../reducers';
import Entities from './Entities';
import { IProps } from './Orchestrator._types';
import Search from './Search';
import SearchResults from './SearchResults';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
})

function Orchestrator({ globalEvents }: IProps) {
    const glossaryRef = useRef<HTMLDivElement>();

    /**
     * Default event handler for the global event `referenceClick`. These references can exist in all forms
     * of entities, so they're centrally managed by the orchestrator.
     */
     const _onGlobalListenerReferenceLoad = useCallback((ev: CustomEvent<IReferenceLinkClickDetails>) => {
        const {
            glossId,
            languageShortName,
            normalizedWord,
            word,
            updateBrowserHistory,
        } = ev.detail;

        // go to the top of the page to ensure that the client understands that the glossary
        // is being reloaded.
        const container = glossaryRef.current;
        if (container) {
            makeVisibleInViewport(container);
        } else {
            window.scrollTo(0, 0);
        }

        const searchActions = new SearchActions();
        store.dispatch<any>(
            glossId //
                ? searchActions.expandSpecificGloss(glossId)
                : searchActions.loadReference(word, normalizedWord, languageShortName, updateBrowserHistory),
        );
    }, []);

    useEffect(() => {
        // Subscribe to the global event `loadReference` which occurs when the customer clicks
        // a reference link in any component not associated with the Glossary app.
        globalEvents.loadReference = _onGlobalListenerReferenceLoad;

        return () => {
            globalEvents.disconnect();
        }
    }, []);

    return <Provider store={store}>
        <Search />
        <SearchResults />
        <div ref={glossaryRef}>
            <Entities />
        </div>
    </Provider>;
}

export default withPropInjection(Orchestrator, {
    globalEvents: DI.GlobalEvents,
});
