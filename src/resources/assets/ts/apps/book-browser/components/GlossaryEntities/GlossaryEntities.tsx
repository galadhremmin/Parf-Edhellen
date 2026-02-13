import { lazy, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { Waypoint } from 'react-waypoint';

import type { ReduxThunkDispatch } from '@root/_types';
import type { IComponentEvent } from '@root/components/Component._types';
import type { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import Cache from '@root/utilities/Cache';

import { SearchActions } from '../../actions';
import type { IBrowserHistoryState } from '../../actions/SearchActions._types';
import type { IEntitiesComponentProps } from '../../containers/Entities._types';
import GlossaryEntitiesEmpty from '../GlossaryEntitiesEmpty';
import GlossaryEntitiesLoading from './GlossaryEntitiesLoading';
import GlossaryLanguages from './GlossaryLanguages';
import GlossaryMinimap from './GlossaryMinimap';
import UnusualLanguagesWarning from './UnusualLanguagesWarning';
import { WordListMembershipProvider } from './WordListMembershipContext';

import './GlossaryEntities.scss';

const GlossaryEntitiesLanguagesConfigKey = 'ed.glossary.unusual-languages';

function GlossaryEntities(props: IEntitiesComponentProps) {
    const languageConfigRef = useRef<Cache<boolean>>();
    const glossaryContainerRef = useRef<HTMLDivElement>();
    const waypointRef = useRef<HTMLDivElement>();
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
        }).catch(err => {
            console.warn(err);
            setShowUnusualLanguages(false);
        });

        const actions = new SearchActions();
        actionsRef.current = actions;

        const __onPopState = onPopState.bind(this, actionsRef.current, actions);
        window.addEventListener('popstate', __onPopState);

        return () => {
            window.removeEventListener('popstate', __onPopState);
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
     * Smoothly scrolls to the glossary content when the user clicks the bouncing arrow.
     */
    const _onScrollToContent = () => {
        waypointRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * `Waypoint` position change handler. Unlike `onEnter`/`onLeave`, `onPositionChange`
     * fires on initial mount — so the arrow correctly appears when the glossary starts
     * below the viewport without requiring a scroll first.
     */
    const _onPositionChange = (ev: Waypoint.CallbackArgs) => {
        const nextNotifyLoaded = (ev.currentPosition === Waypoint.below);

        if (notifyLoaded !== nextNotifyLoaded) {
            setNotifyLoaded(nextNotifyLoaded);
        }
    }

    // Combine visible languages for the minimap — common + unusual (if shown)
    const showUnusual = (forceShowUnusualLanguages || showUnusualLanguages) && unusualLanguages?.length > 0;
    const minimapLanguages = useMemo(() => {
        const langs = [...(commonLanguages || [])];
        if (showUnusual && unusualLanguages) {
            langs.push(...unusualLanguages);
        }
        return langs;
    }, [commonLanguages, unusualLanguages, showUnusual]);

    const showMinimap = ! loading && ! isEmpty && ! single && minimapLanguages.length >= 2;

    return <div className="ed-glossary-container" ref={glossaryContainerRef}>
        {notifyLoaded && <FixedBouncingArrow onClick={_onScrollToContent} />}
        {showMinimap && <GlossaryMinimap languages={minimapLanguages} sections={sections} />}
        {loading && <GlossaryEntitiesLoading minHeight={glossaryContainerRef.current?.offsetHeight || 500} />}
        {! loading && isEmpty && <GlossaryEntitiesEmpty word={word} />}
        {! loading && ! isEmpty && <WordListMembershipProvider sections={sections}>
            <Waypoint onPositionChange={_onPositionChange} bottomOffset="50%">
                <div className="ed-glossary-waypoint" ref={waypointRef}>
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
                        {showUnusual && <GlossaryLanguages
                            className="ed-glossary--unusual"
                            languages={unusualLanguages}
                            entityMorph={entityMorph}
                            sections={sections}
                            single={single}
                            onReferenceClick={onReferenceClick}
                        />}
                    </>}
                </div>
            </Waypoint>
        </WordListMembershipProvider>}
    </div>;
}

function createLanguageConfig(): Cache<boolean> {
    const falsyResolver = () => Promise.resolve(false);
    return Cache.withPersistentStorage(falsyResolver, GlossaryEntitiesLanguagesConfigKey);
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
   const globalEvents = resolve(DI.GlobalEvents);
   globalEvents.fire(globalEvents.loadReference, ev.value);
}

const BouncingArrowAsync = lazy(() => import('@root/components/BouncingArrow'));
const FixedBouncingArrow = (props: any) => <Suspense fallback={null}>
    <div className="ed-glossary-loaded-notifier">
        <BouncingArrowAsync {...props} />
    </div>
</Suspense>;

export default GlossaryEntities;
