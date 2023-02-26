import {
    useCallback,
    useEffect,
    useRef,
} from 'react';
import { Provider } from 'react-redux';import {
    applyMiddleware,
    createStore,
} from 'redux';
import thunkMiddleware from 'redux-thunk';

import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';
import { composeEnhancers } from '@root/utilities/func/redux-tools';

import rootReducer from '../reducers';
import Entities from './Entities';
import Search from './Search';
import SearchResults from './SearchResults';
import { SearchActions } from '../actions';

const store = createStore(rootReducer, undefined,
    composeEnhancers('book-browser')(applyMiddleware(thunkMiddleware)),
);

function Orchestrator() {
    const globalConnectorRef = useRef<GlobalEventConnector>();
    const glossaryRef = useRef<HTMLDivElement>();

    /**
     * Default event handler for the global event `referenceClick`. These references can exist in all forms
     * of entities, so they're centrally managed by the orchestrator.
     */
     const _onGlobalListenerReferenceLoad = useCallback((ev: CustomEvent<IReferenceLinkClickDetails>) => {
        const {
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
            searchActions.loadReference(word, normalizedWord, languageShortName, updateBrowserHistory),
        );
    }, []);

    useEffect(() => {
        // Subscribe to the global event `loadReference` which occurs when the customer clicks
        // a reference link in any component not associated with the Glossary app.
        const connector = new GlobalEventConnector();
        connector.loadReference = _onGlobalListenerReferenceLoad;
        globalConnectorRef.current = connector;

        return () => {
            globalConnectorRef.current?.disconnect();
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

export default Orchestrator;
