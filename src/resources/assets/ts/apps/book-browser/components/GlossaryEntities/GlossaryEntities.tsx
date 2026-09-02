import { lazy, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { Waypoint } from 'react-waypoint';

import type { ReduxThunkDispatch } from '@root/_types';
import type { IComponentEvent } from '@root/components/Component._types';
import type { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import Cache from '@root/utilities/Cache';

import { SearchActions } from '../../actions';
import { hasGlossaryChangedAddress } from '../../actions/SearchActions';
import type { IBrowserHistoryState } from '../../actions/SearchActions._types';
import type { IEntitiesComponentProps } from '../../containers/Entities._types';
import CurrentLanguagesDivider from './CurrentLanguagesDivider';
import GlossaryEntitiesEmpty from '../GlossaryEntitiesEmpty';
import GlossaryEntitiesLoading from './GlossaryEntitiesLoading';
import GlossaryLanguages from './GlossaryLanguages';
import GlossaryMinimap from './GlossaryMinimap';
import UnusualLanguagesWarning from './UnusualLanguagesWarning';
import { LanguageLookupProvider } from './LanguageLookupContext';
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
        dispatch,
        entityMorph,
        forceShowUnusualLanguages,
        languageDictionary,
        languages: commonLanguages,
        leadWithUnusual,
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

        const __onPopState = onPopState.bind(this, actionsRef.current, dispatch);
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

    // Combine visible languages for the minimap — common + unusual (if shown), in whichever order they render.
    const showUnusual = unusualLanguages?.length > 0 && (leadWithUnusual || forceShowUnusualLanguages || showUnusualLanguages);
    const minimapLanguages = useMemo(() => {
        const common = commonLanguages || [];
        const unusual = showUnusual && unusualLanguages ? unusualLanguages : [];
        return leadWithUnusual ? [...unusual, ...common] : [...common, ...unusual];
    }, [commonLanguages, unusualLanguages, showUnusual, leadWithUnusual]);

    const showMinimap = ! loading && ! isEmpty && ! single && minimapLanguages.length >= 2;

    return <div className="ed-glossary-container" ref={glossaryContainerRef}>
        {notifyLoaded && <FixedBouncingArrow onClick={_onScrollToContent} />}
        {showMinimap && <GlossaryMinimap languages={minimapLanguages} sections={sections} />}
        {loading && <GlossaryEntitiesLoading minHeight={glossaryContainerRef.current?.offsetHeight || 500} />}
        {! loading && isEmpty && <GlossaryEntitiesEmpty word={word} />}
        {! loading && ! isEmpty && <LanguageLookupProvider languages={languageDictionary || []}>
            <WordListMembershipProvider sections={sections}>
                <Waypoint onPositionChange={_onPositionChange} bottomOffset="50%">
                    <div className="ed-glossary-waypoint" ref={waypointRef}>
                        {/* The single best-rated entry overall is a genuine direct match and lives in an
                            "unusual" (older/rejected conceptual period) language — lead with it, fully shown
                            (no opt-in gate — it's the right word, not just the least-bad fuzzy hit), instead
                            of burying it below the normal languages. A divider then marks where the current
                            languages resume, since they'd otherwise be hard to spot below the older ones. */}
                        {leadWithUnusual && unusualLanguages?.length > 0 && <>
                            <UnusualLanguagesWarning />
                            <GlossaryLanguages
                                className="ed-glossary--unusual"
                                languages={unusualLanguages}
                                entityMorph={entityMorph}
                                featureBestMatch={true}
                                sections={sections}
                                single={single}
                                word={word}
                                onReferenceClick={onReferenceClick}
                            />
                            <CurrentLanguagesDivider />
                        </>}
                        <GlossaryLanguages
                            languages={commonLanguages}
                            entityMorph={entityMorph}
                            featureBestMatch={true}
                            sections={sections}
                            single={single}
                            word={word}
                            onReferenceClick={onReferenceClick}
                        />
                        {! leadWithUnusual && unusualLanguages?.length > 0 && <>
                            <UnusualLanguagesWarning
                                showOverrideOption={! forceShowUnusualLanguages && ! showUnusualLanguages}
                                onOverrideOptionTriggered={_onUnusualLanguagesShowClick}
                            />
                            {showUnusual && <GlossaryLanguages
                                className="ed-glossary--unusual"
                                languages={unusualLanguages}
                                entityMorph={entityMorph}
                                featureBestMatch={true}
                                sections={sections}
                                single={single}
                                word={word}
                                onReferenceClick={onReferenceClick}
                            />}
                        </>}
                    </div>
                </Waypoint>
            </WordListMembershipProvider>
        </LanguageLookupProvider>}
    </div>;
}

function createLanguageConfig(): Cache<boolean> {
    const falsyResolver = () => Promise.resolve(false);
    return Cache.withPersistentStorage(falsyResolver, GlossaryEntitiesLanguagesConfigKey);
}

function onPopState(actions: SearchActions, dispatch: ReduxThunkDispatch, ev: PopStateEvent) {
    const state = ev.state as IBrowserHistoryState;
    if (! state || ! state.glossary) {
        // An address the glossary never created, which means the server rendered it — so only the
        // server can bring it back. Reloading is the honest answer; leaving the page as it is would
        // show the previous entry under an address that has nothing to do with it.
        if (hasGlossaryChangedAddress()) {
            window.location.reload();
        }
        return;
    }

    onReferenceClick({
        value: {
            ...state,
            updateBrowserHistory: false,
        },
    });
    // `state.word` is the resolved headword, but the search suggestions list is keyed by the literal
    // matched keyword — which is `state.inflection` when this navigation came from an inflected form.
    dispatch(
        actions.selectSearchResultByWord(state.inflection || state.word),
    );
}

/**
* Default event handler for reference link clicks.
*/
function onReferenceClick(ev: IComponentEvent<IReferenceLinkClickDetails>) {
   const globalEvents = resolve(DI.GlobalEvents);
   globalEvents?.fire(globalEvents.loadReference, ev.value);
}

const BouncingArrowAsync = lazy(() => import('@root/components/BouncingArrow'));
const FixedBouncingArrow = (props: any) => <Suspense fallback={null}>
    <div className="ed-glossary-loaded-notifier">
        <BouncingArrowAsync {...props} />
    </div>
</Suspense>;

export default GlossaryEntities;
