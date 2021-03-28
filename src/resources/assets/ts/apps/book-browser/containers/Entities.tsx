import React, { useEffect, Suspense, useState } from 'react';
import { connect } from 'react-redux';

import { SearchResultGroups } from '@root/config';
import { IEntitiesResponse } from '@root/connectors/backend/IBookApi';
import { snakeCasePropsToCamelCase } from '@root/utilities/func/snake-case';
import { SearchActions } from '../actions';
import { RootReducer } from '../reducers';
import { IEntitiesComponentProps } from './Entities._types';
import LoadingIndicator from '../components/LoadingIndicator';

function Entities(props: IEntitiesComponentProps) {
    // This is the component that will be used to render the entities.
    const [ componentName, setComponentName ] = useState<string>(null);

    const {
        dispatch,
        groupId,
    } = props;

    /**
     * Initializes the component with the glossary supplied to it from the server. This is
     * a common scenario when the client reloads the browser (and consequently loses JS in-memory state).
     * In this scenario, the server will provide the glossary for the page that was just loaded, and the
     * JavaScript client is responsible to pick it up and use it to restore its previous in-memory state.
     */
    useEffect(() => {
        const preloadedEntities = _getPreloadedEntities();
        if (preloadedEntities) {
            const actions = new SearchActions();
            dispatch(actions.setEntities(preloadedEntities));
        }
        _removeEntitiesForBots();
    }, []);

    useEffect(() => {
        if (groupId < 1) {
            setComponentName(null);
        } else {
            const nextComponentName = SearchResultGroups[groupId];
            if (nextComponentName === undefined) {
                throw new Error(`Unrecognised entities group ID: ${groupId}. There's no renderer that supports this group.`);
            }
            setComponentName(nextComponentName);
        }
    }, [ groupId ]);

    if (componentName === null) {
        return null;
    }

    const Component = React.lazy(() => import(`../components/${componentName}Entities`));
    return <Suspense fallback={<LoadingIndicator text="Setting things up..." />}>
        <Component {...props} />
    </Suspense>;
}

/**
 * Gets the text content of an arbitrary element with the id `ed-preloaded-book` and deserializes
 * it using the JSON serializer. The element is expected to contain a full glossary API request
 * response.
 */
const _getPreloadedEntities = () => {
    const stateContainer = document.getElementById('ed-preloaded-book');
    if (!stateContainer) {
        return null;
    }

    try {
        const entities = JSON.parse(stateContainer.textContent);

        // The glossary is preloaded by the server, so its properties are `snake_case`.
        // Consequently, we must convert them to `camelCase` which is recognised by the view.
        return snakeCasePropsToCamelCase<IEntitiesResponse<any>>(entities);
    } catch (e) {
        // We do not really care about these errors -- just silence the exception when
        // the format is unrecognised.
        console.warn(e);
        return null;
    }
}

/**
 * Removes an element with the id `ed-book-for-bots` which is a partial server-side rendering
 * of the glossary intended for search indexing purposes.
 */
const _removeEntitiesForBots = () => {
    // has the server added a glossary intended for bots (such as Google)?
    // remove them, if such is the case:
    const entitiesForBots = document.getElementById('ed-book-for-bots');
    if (entitiesForBots) {
        entitiesForBots.parentElement.removeChild(entitiesForBots);
    }
}

const mapStateToProps = (state: RootReducer): IEntitiesComponentProps => ({
    groupId: state.entities.groupId,
    loading: state.entities.loading,
    single: state.entities.single,
    word: state.entities.word,

    isEmpty: state.languages.isEmpty,
    glosses: state.glosses,
    languages: state.languages.common,
    unusualLanguages: state.languages.unusual,
});

export default connect(mapStateToProps)(Entities);
