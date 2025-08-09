import React from 'react';
import {
    connect,
} from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';
import LanguageSelect from '@root/components/Form/LanguageSelect';
import TextIcon from '@root/components/TextIcon';
import Cache from '@root/utilities/Cache';
import debounce from '@root/utilities/func/debounce';
import { excludeProps } from '@root/utilities/func/props';
import { SearchActions } from '../actions';
import AdditionalSearchParameters from '../components/AdditionalSearchParameters';
import SearchQueryInput from '../components/SearchQueryInput';
import { RootReducer } from '../reducers';

import {
    IProps,
    IState,
} from './Search._types';

import './Search.scss';

// TODO: Migrate to React component `function`
export class SearchQuery extends React.Component<IProps, IState> {

    public state: IState;

    private _actions: SearchActions;
    private _stateCache: Cache<IState>;
    private _beginSearch: (queryChanged: boolean) => void;

    constructor(props: IProps) {
        super(props);

        const defaultState = {
            lexicalEntryGroupIds: [0],
            includeOld: props.includeOld,
            languageId: props.languageId,
            naturalLanguage: props.naturalLanguage,
            reversed: props.reversed,
            showMore: false,
            speechIds: [0],
            word: props.word,
        };

        // The component maintains its own transient state between the last search (maintained by Redux)
        // and current state (maintained by the component). The two states converge when the search command
        // is executed.
        this.state = defaultState;

        this._actions = new SearchActions();
        this._beginSearch = debounce(500, this._search);
        this._stateCache = Cache.withPersistentStorage(
            () => Promise.resolve(defaultState),
            'ed.search-state.v3',
        );
    }

    public componentDidMount() {
        this._stateCache.get().then((persistedState) => {
            if (persistedState) {
                // TODO: When languageId is invalid, it should be reset to 0. To achieve this, we should
                //       check the languageId against the list of languages available via ILanguageApi.
                this.setState(persistedState);
            }
        }).catch(() => {
            // Should already be default state, so nothing needs to be done.
        })
    }

    public render() {
        const {
            loading,
        } = this.props;
        const {
            lexicalEntryGroupIds,
            includeOld,
            languageId,
            naturalLanguage,
            reversed,
            showMore,
            speechIds,
            word,
        } = this.state;

        return <form onSubmit={this._onSubmit} className="Search container-fluid">
            <div className="row">
                <div className="col">
                    <SearchQueryInput
                        name="query"
                        loading={loading}
                        onChange={this._onQueryChange}
                        onSearchResultNavigate={this._onSearchResultNavigate}
                        tabIndex={1}
                        value={word}
                    />
                </div>
            </div>
            <div className="row Search--config mt-2">
                <div className="col">
                    <label className="ms-2">
                        <input checked={reversed}
                            name="reversed"
                            onChange={this._onReverseChange}
                            type="checkbox"
                        /> Reverse
                    </label>
                    <label className="ms-2">
                        <input checked={naturalLanguage}
                            name="naturalLanguage"
                            onChange={this._onNaturalLanguageChange}
                            type="checkbox"
                        /> Natural language
                    </label>
                    <label className="ms-2">
                        <input checked={includeOld}
                            name="includeOld"
                            onChange={this._onIncludeOldChange}
                            type="checkbox"
                        /> Incl. outdated
                    </label>
                    <div className="ms-2 d-inline-block">
                        <LanguageSelect
                            name="languageId"
                            onChange={this._onLanguageChange}
                            value={languageId}
                        />
                        <a href="#" onClick={this._onShowMoreClick} className="Search--config__expand">
                            {showMore ? <>
                                <TextIcon icon="minus-sign" />
                                <span>Less options</span>
                            </> : <>
                                <TextIcon icon="plus-sign" />
                                <span>More options</span>
                            </>}
                        </a>
                    </div>
                </div>
            </div>
            <div className="row">
                <div className="col">
                    {showMore && <AdditionalSearchParameters
                        lexicalEntryGroupId={lexicalEntryGroupIds[0]}
                        onLexicalEntryGroupIdChange={this._onGlossGroupIdChange}
                        onSpeechIdChange={this._onSpeechIdChange}
                        speechId={speechIds[0]}
                    />}
                </div>
            </div>
        </form>;
    }

    private _onQueryChange = (ev: IComponentEvent<string>) => {
        this.setState({
            word: ev.value,
        });

        this._beginSearch(/* queryChanged: */ true);
    }

    private _onShowMoreClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        const state = this.state;
        let {
            showMore,
        } = state;

        showMore = !showMore;

        const nextState: Partial<IState> = {
            showMore,
        };
        if (! showMore) {
            // reset default configuration
            nextState.lexicalEntryGroupIds = [0];
            nextState.speechIds     = [0];
        }

        this.setState(nextState);
        void this._persistState(
            excludeProps({
                ...state,
                ...nextState,
            }, ['word']),
        );
    }

    private _onReverseChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const reversed = ev.target.checked;
        this.setState({
            reversed,
        });

        void this._persistState('reversed', reversed);
        this._beginSearch(/* queryChanged: */ true);
    }

    private _onNaturalLanguageChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const naturalLanguage = ev.target.checked;
        this.setState({
            naturalLanguage,
        });

        void this._persistState('naturalLanguage', naturalLanguage);
        this._beginSearch(/* queryChanged: */ false);
    }

    private _onIncludeOldChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const includeOld = ev.target.checked;
        this.setState({
            includeOld,
        });

        void this._persistState('includeOld', includeOld);
        this._beginSearch(/* queryChanged: */ false);
    }

    private _onLanguageChange = (ev: IComponentEvent<number>) => {
        const languageId = ev.value;
        this.setState({
            languageId,
        });

        void this._persistState('languageId', languageId);
        this._beginSearch(/* queryChanged: */ false);
    }

    private _onGlossGroupIdChange = (ev: IComponentEvent<number>) => {
        const lexicalEntryGroupIds = [ev.value || 0];
        this.setState({
            lexicalEntryGroupIds,
        });

        void this._persistState('lexicalEntryGroupIds', lexicalEntryGroupIds);
        this._beginSearch(/* queryChanged: */ false);
    }

    private _onSpeechIdChange = (ev: IComponentEvent<number>) => {
        this.setState({
            speechIds: [ev.value || 0],
        });

        this._beginSearch(/* queryChanged: */ false);
    }

    private _onSubmit = (ev: React.FormEvent<HTMLFormElement>) => {
        ev.preventDefault();
    }

    /**
     * Default event handler for navigating search results with the arrow
     * keys and the enter key.
     */
    private _onSearchResultNavigate = (ev: IComponentEvent<number>) => {
        void this.props.dispatch(
            this._actions.selectNextResult(ev.value),
        );
    }

    /**
     * Performs a keyword search operation, *and* refreshes the glossary if filters
     * changed.
     */
    private _search = (queryChanged: boolean) => {
        const state = this.state;
        void this.props.dispatch(
            this._actions.search({
                ...state,
                // These hacks only accommodates for the fact that the UI does not currently support
                // multiple selections.
                lexicalEntryGroupIds: state.lexicalEntryGroupIds[0] === 0 ? [] : state.lexicalEntryGroupIds,
                speechIds: state.speechIds[0] === 0 ? [] : state.speechIds,
            }),
        );

        // If the user has only made changes to the filtering functions (such as language selection),
        // *and* has a previous glossary already loaded, the user expects the changes to their configuration
        // to reflect to the glossary currently loaded.
        if (queryChanged === false && this.props.currentGlossaryWord?.length > 0) {
            void this.props.dispatch(
                this._actions.reloadGlossary(),
            );
        }
    }

    private async _persistState<T extends keyof IState>(keyOrState: T | IState, value?: IState[T]) {
        const state = await this._stateCache.get();

        if (typeof keyOrState === 'string') {
            this._stateCache.set({
                ...state,
                [keyOrState]: value,
            });
        } else {
            this._stateCache.set(keyOrState);
        }
    }
}

const mapStateToProps = (state: RootReducer) => ({
    currentGlossaryWord: state.entities.word,
    includeOld: state.search.includeOld,
    languageId: state.search.languageId,
    loading: state.search.loading,
    naturalLanguage: state.search.naturalLanguage,
    reversed: state.search.reversed,
    word: state.search.word,
});

export default connect(mapStateToProps)(SearchQuery);
