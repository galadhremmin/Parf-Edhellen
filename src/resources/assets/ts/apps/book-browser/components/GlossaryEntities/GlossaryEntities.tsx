import React from 'react';

import { Waypoint } from 'react-waypoint';

import { IComponentEvent } from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import Cache from '@root/utilities/Cache';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import { SearchActions } from '../../actions';
import { IBrowserHistoryState } from '../../actions/SearchActions._types';
import { IEntitiesComponentProps } from '../../containers/Entities._types';
import { IState } from './GlossaryEntities._types';
import GlossaryLanguage from './GlossaryLanguage';
import LoadingIndicator from '../LoadingIndicator';

import './GlossaryEntities.scss';

export default class GlossaryEntities extends React.Component<IEntitiesComponentProps, IState> {
    public state: IState = {
        notifyLoaded: true,
        showUnusualLanguages: false,
    };

    private _glossaryContainerRef: React.RefObject<HTMLDivElement>;
    private _actions = new SearchActions();
    private _globalEvents = new GlobalEventConnector();
    private _unusualLanguagesConfig: Cache<boolean>;

    constructor(props: IEntitiesComponentProps) {
        super(props);
        this._glossaryContainerRef = React.createRef<HTMLDivElement>();

        const languagesConfigKey = 'ed.glossary.unusual-languages';
        try {
            this._unusualLanguagesConfig = Cache.withLocalStorage<boolean>(() => Promise.resolve(false), languagesConfigKey);
        } catch (e) {
            // Probably a unit test
            this._unusualLanguagesConfig = Cache.withMemoryStorage<boolean>(() => Promise.resolve(false), languagesConfigKey);
        }
    }

    public async componentDidMount() {
        // Subscribe to the global event `loadReference` which occurs when the customer clicks
        // a reference link in any component not associated with the Glossary app.
        this._globalEvents.loadReference = this._onGlobalListenerReferenceLoad;
        window.addEventListener('popstate', this._onPopState);

        const showUnusualLanguages = await this._unusualLanguagesConfig.get();
        this.setState({
            showUnusualLanguages,
        });
    }

    public componentWillUnmount() {
        window.removeEventListener('popstate', this._onPopState);
        this._globalEvents.disconnect();
    }

    public render() {
        return <div className="ed-glossary-container" ref={this._glossaryContainerRef}>
            {this._renderViews()}
        </div>;
    }

    private _renderViews() {
        const {
            isEmpty,
            loading,
        } = this.props;

        if (loading) {
            return this._renderLoading();
        }

        if (isEmpty) {
            return this._renderEmptyDictionary();
        }

        return this._renderDictionary();
    }

    private _renderLoading() {
        const lastHeight = this._glossaryContainerRef.current?.offsetHeight || 500;
        const heightStyle = {
            minHeight: `${lastHeight}px`,
        };

        return <div style={heightStyle}>
            <LoadingIndicator text="Retrieving glossary..." />
        </div>;
    }

    private _renderEmptyDictionary() {
        return <div>
            <h3>Alas! What you are looking for does not exist!</h3>
            <p>The word <em>{this.props.word}</em> does not exist in the dictionary.</p>
        </div>;
    }

    private _renderDictionary() {
        return <React.Fragment>
            {this.state.notifyLoaded && <FixedBouncingArrow />}
            <Waypoint onEnter={this._onWaypointEnter} onLeave={this._onWaypointLeave}>
                <article>
                    {this._renderCommonLanguages()}
                    {this._renderUnusualLanguages()}
                </article>
            </Waypoint>
        </React.Fragment>;
    }

    private _renderCommonLanguages() {
        if (this.props.languages.length === 0) {
            return null;
        }

        return this._renderLanguages(this.props.languages);
    }

    private _renderUnusualLanguages() {
        const {
            showUnusualLanguages,
        } = this.state;

        if (this.props.unusualLanguages.length === 0) {
            return null;
        }

        const abstract = <>
            <h3>
                There are more words but they are from Tolkien's earlier conceptional periods
            </h3>
            <p>
                Tolkien likely changed these words as he evolved the aesthetics and completeness of the languages. You may even find
                languages that Tolkien later rejected. Do not mix words from different time periods unless you are familiar with the
                phonetic developments.
            </p>
        </>;

        if (showUnusualLanguages) {
            return this._renderLanguages(this.props.unusualLanguages, abstract, ['ed-glossary--unusual']);
        } else {
            return <div className="text-center">
                {abstract}
                <p>
                    You can view these words by clicking the button below. You will not be asked again (unless you clear your browser's local storage!)
                </p>
                <button className="btn btn-default" onClick={this._onUnusualLanguagesShowClick}>I understand - show me the words!</button>
            </div>;
        }
    }

    private _renderLanguages(languages: ILanguageEntity[], abstract: React.ReactNode = null, //
        classNames: string[] = []) {
        classNames = [ 'ed-glossary', ...classNames ];
        if (this.props.single) {
            classNames.push('ed-glossary--single');
        }

        return <section className={classNames.join(' ')}>
            {abstract}
            {languages.map(
                (language) => <GlossaryLanguage key={language.id} language={language}
                    glosses={this.props.sections[language.id]} onReferenceLinkClick={this._onReferenceClick} />,
            )}
        </section>;
    }

    /**
     * Default event handler for reference link clicks.
     */
    private _onReferenceClick = async (ev: IComponentEvent<IReferenceLinkClickDetails>) => {
        this.props.dispatch(
            this._actions.loadReference(ev.value.word, ev.value.normalizedWord, ev.value.languageShortName,
                ev.value.updateBrowserHistory),
        );
    }

    /**
     * `Waypoint` default event handler for the `enter` event. It is used to track the notifier arrow,
     * and hiding it when the user is viewing the glossary.
     */
    private _onWaypointEnter = () => {
        const notifyLoaded = false;

        if (this.state.notifyLoaded !== notifyLoaded) {
            this.setState({
                notifyLoaded,
            });
        }
    }

    /**
     * `Waypoint` default event handler for the `leave` event. It is used to track the notifier arrow,
     * and showing it when the component is below the viewport.
     */
    private _onWaypointLeave = (ev: Waypoint.CallbackArgs) => {
        // `currentPosition` is the position of the element relative to the viewport.
        const notifyLoaded = (ev.currentPosition === Waypoint.below);

        if (this.state.notifyLoaded !== notifyLoaded) {
            this.setState({
                notifyLoaded,
            });
        }
    }

    /**
     * Default event handler for the global event `referenceClick`.
     */
    private _onGlobalListenerReferenceLoad = (ev: CustomEvent) => {
        // go to the top of the page to ensure that the client understands that the glossary
        // is being reloaded.
        if (this._glossaryContainerRef.current) {
            makeVisibleInViewport(this._glossaryContainerRef.current);
        } else {
            window.scrollTo(0, 0);
        }

        this._onReferenceClick({
            value: ev.detail,
        });
    }

    private _onPopState = (ev: PopStateEvent) => {
        const state = ev.state as IBrowserHistoryState;
        if (! state || ! state.glossary) {
            return;
        }

        this._onReferenceClick({
            value: {
                ...state,
                updateBrowserHistory: false,
            },
        });
        this.props.dispatch(
            this._actions.selectSearchResultByWord(state.word),
        );
    }

    /**
     * Permanently (well, in local storage) displays languages from Tolkien's earlier conceptual periods.
     */
    private _onUnusualLanguagesShowClick = () => {
        const showUnusualLanguages = true;
        this.setState({
            showUnusualLanguages,
        });
        this._unusualLanguagesConfig.set(showUnusualLanguages);
    }
}

const BouncingArrowAsync = React.lazy(() => import('@root/components/BouncingArrow'));
const FixedBouncingArrow = (props: any) => <React.Suspense fallback={null}>
    <div className="ed-glossary-loaded-notifier">
        <BouncingArrowAsync {...props} />
    </div>
</React.Suspense>;
