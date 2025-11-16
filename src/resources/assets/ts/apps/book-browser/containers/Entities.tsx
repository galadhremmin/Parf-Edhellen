import { lazy, useEffect, Suspense, useState } from 'react';
import type { ComponentType } from 'react';
import { connect } from 'react-redux';

import type { IEntitiesResponse } from '@root/connectors/backend/IBookApi';
import { snakeCasePropsToCamelCase } from '@root/utilities/func/snake-case';
import { SearchActions } from '../actions';
import type { RootReducer } from '../reducers';
import type { IEntitiesComponentProps } from './Entities._types';
import LoadingIndicator from '../components/LoadingIndicator';
import { capitalize } from '@root/utilities/func/string-manipulation';

function Entities(props: IEntitiesComponentProps) {
    // This is the component that will be used to render the entities.
    const [ Component, setComponent ] = useState<ComponentType<IEntitiesComponentProps>>(null);
    const [ componentGroupName, setComponentGroupName ] = useState<string | null>(null);

    const {
        dispatch,
        groupName,
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
        if (! groupName) {
            setComponent(null);
            setComponentGroupName(null);
        } else {
            setComponent(lazy(() => import(`../components/${capitalize(groupName)}Entities`)));
            setComponentGroupName(groupName);
        }
    }, [ groupName ]);

    if (Component === null) {
        return null;
    }

    const LoadingComponent = <LoadingIndicator text="Setting things up..." />;

    return <Suspense fallback={LoadingComponent}>
        {componentGroupName !== groupName ? LoadingComponent : <Component {...props} />}
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
    entityMorph: state.entities.entityMorph,
    groupId: state.entities.groupId,
    groupName: state.entities.groupIntlName,
    loading: state.entities.loading,
    sections: state.sections,
    single: state.entities.single,
    word: state.entities.word,

    // Glossary
    isEmpty: state.categories.isEmpty,
    languages: state.categories.common,
    unusualLanguages: state.categories.unusual,
});

export default connect(mapStateToProps)(Entities);
