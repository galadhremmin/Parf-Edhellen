import React, { useEffect, useRef, useState } from 'react';
import { Waypoint } from 'react-waypoint';

import { IComponentEvent } from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import Cache from '@root/utilities/Cache';
import { ReduxThunkDispatch } from '@root/_types';

import { SearchActions } from '../../actions';
import { IBrowserHistoryState } from '../../actions/SearchActions._types';
import { IEntitiesComponentProps } from '../../containers/Entities._types';

import GlossaryEntitiesEmpty from '../GlossaryEntitiesEmpty';
import './GlossaryEntities.scss';
import GlossaryEntitiesLoading from './GlossaryEntitiesLoading';
import GlossaryLanguages from './GlossaryLanguages';
import UnusualLanguagesWarning from './UnusualLanguagesWarning';

const GlossaryEntitiesLanguagesConfigKey = 'ed.glossary.unusual-languages';

function GlossaryEntities(props: IEntitiesComponentProps) {
    const languageConfigRef = useRef<Cache<boolean>>();
    const glossaryContainerRef = useRef<HTMLDivElement>();
    const actionsRef = useRef<SearchActions>();

    const [ notifyLoaded, setNotifyLoaded ] = useState<boolean>(false);
    const [ showUnusualLanguages, setShowUnusualLanguages ] = useState<boolean>(false);

    const {
        entityMorph,
        forceShowUnusualLanguages,
        languages: commonLanguages,
        loading,
        isEmpty,
        sections,
        single,
        unusualLanguages,
        word,
    } = props;

    useEffect(() => {
        const config = createLanguageConfig();
        (languageConfigRef.current = config).get().then((shouldShowUnusualLanguages) => {
            setShowUnusualLanguages(shouldShowUnusualLanguages);
        });

        const actions = new SearchActions();
        actionsRef.current = actions;

        const _onPopState = onPopState.bind(this, actionsRef.current, actions);
        window.addEventListener('popstate', _onPopState);

        return () => {
            window.removeEventListener('popstate', _onPopState);
        };
    }, []);

    /**
     * Permanently (well, in local storage) displays languages from Tolkien's earlier conceptual periods.
     */
    const _onUnusualLanguagesShowClick = () => {
        setShowUnusualLanguages(true);
        languageConfigRef.current?.set(true);
    }

    /**
     * `Waypoint` default event handler for the `enter` event. It is used to track the notifier arrow,
     * and hiding it when the user is viewing the glossary.
     */
    const _onWaypointEnter = () => {
        const nextNotifyLoaded = false;

        if (notifyLoaded !== nextNotifyLoaded) {
            setNotifyLoaded(nextNotifyLoaded);
        }
    }

    /**
     * `Waypoint` default event handler for the `leave` event. It is used to track the notifier arrow,
     * and showing it when the component is below the viewport.
     */
    const _onWaypointLeave = (ev: Waypoint.CallbackArgs) => {
        // `currentPosition` is the position of the element relative to the viewport.
        const nextNotifyLoaded = (ev.currentPosition === Waypoint.below);

        if (notifyLoaded !== nextNotifyLoaded) {
            setNotifyLoaded(nextNotifyLoaded);
        }
    }

    return <div className="ed-glossary-container" ref={glossaryContainerRef}>
        {notifyLoaded && <FixedBouncingArrow />}
        {loading && <GlossaryEntitiesLoading minHeight={glossaryContainerRef.current?.offsetHeight || 500} />}
        {! loading && isEmpty && <GlossaryEntitiesEmpty word={word} />}
        {! loading && ! isEmpty && <Waypoint onEnter={_onWaypointEnter} onLeave={_onWaypointLeave}>
            <div className="ed-glossary-waypoint">
                <GlossaryLanguages
                    languages={commonLanguages}
                    entityMorph={entityMorph}
                    sections={sections}
                    single={single}
                    onReferenceClick={onReferenceClick}
                />
                {unusualLanguages?.length > 0 && <>
                    <UnusualLanguagesWarning
                        showOverrideOption={! forceShowUnusualLanguages && ! showUnusualLanguages}
                        onOverrideOptionTriggered={_onUnusualLanguagesShowClick}
                    />
                    {(forceShowUnusualLanguages || showUnusualLanguages) && <GlossaryLanguages
                        className="ed-glossary--unusual"
                        languages={unusualLanguages}
                        entityMorph={entityMorph}
                        sections={sections}
                        single={single}
                        onReferenceClick={onReferenceClick}
                    />}
                </>}
            </div>
        </Waypoint>}
    </div>;
}

function createLanguageConfig(): Cache<boolean> {
    const falsyResolver = () => Promise.resolve(false);
    try {
        return Cache.withLocalStorage(falsyResolver, GlossaryEntitiesLanguagesConfigKey);
    } catch (e) {
        // Probably a unit test
        return Cache.withMemoryStorage(falsyResolver, GlossaryEntitiesLanguagesConfigKey);
    }
}

function onPopState(actions: SearchActions, dispatch: ReduxThunkDispatch, ev: PopStateEvent) {
    const state = ev.state as IBrowserHistoryState;
    if (! state || ! state.glossary) {
        return;
    }

    onReferenceClick({
        value: {
            ...state,
            updateBrowserHistory: false,
        },
    });
    dispatch(
        actions.selectSearchResultByWord(state.word),
    );
}

/**
* Default event handler for reference link clicks.
*/
function onReferenceClick(ev: IComponentEvent<IReferenceLinkClickDetails>) {
   const globalEvents = new GlobalEventConnector();
   globalEvents.fire(globalEvents.loadReference, ev.value);
}

const BouncingArrowAsync = React.lazy(() => import('@root/components/BouncingArrow'));
const FixedBouncingArrow = (props: any) => <React.Suspense fallback={null}>
    <div className="ed-glossary-loaded-notifier">
        <BouncingArrowAsync {...props} />
    </div>
</React.Suspense>;

export default GlossaryEntities;
