import classNames from 'classnames';
import React from 'react';
import {
    connect,
} from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';
import LanguageSelect from '@root/components/Form/LanguageSelect';
import TextIcon from '@root/components/TextIcon';
import Cache from '@root/utilities/Cache';
import debounce from '@root/utilities/func/debounce';
import { SearchActions } from '../actions';
import SearchQueryInput from '../components/SearchQueryInput';
import { RootReducer } from '../reducers';

import {
    IProps,
    IState,
} from './Search._types';

import './Search.scss';
import { excludeProps } from '@root/utilities/func/props';

const AdditionalSearchParametersAsync = React.lazy(() => import('../components/AdditionalSearchParameters'));

export class SearchQuery extends React.Component<IProps, IState> {

    public state: IState;

    private _actions: SearchActions;
    private _stateCache: Cache<IState>;
    private _beginSearch: (queryChanged: boolean) => void;

    constructor(props: IProps) {
        super(props);

        const defaultState = {
            glossGroupIds: [0],
            includeOld: props.includeOld,
            languageId: props.languageId,
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
        this._stateCache = Cache.withLocalStorage(
            () => Promise.resolve(defaultState),
            'ed.search-state.v2',
        );
    }

    public async componentDidMount() {
        const persistedState = await this._stateCache.get();
        if (persistedState) {
            this.setState(persistedState);
        }
    }

    public render() {
        const {
            loading,
        } = this.props;
        const {
            glossGroupIds,
            includeOld,
            languageId,
            reversed,
            showMore,
            speechIds,
            word,
        } = this.state;

        return <form onSubmit={this._onSubmit} className="Search container-fluid">
            <div className="row">
                <div className="col">
                    <SearchQueryInput
                        autoFocus={true}
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
                        <input checked={includeOld}
                            name="excludeOld"
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
                    {showMore && <React.Suspense fallback={null}>
                        <AdditionalSearchParametersAsync
                            glossGroupId={glossGroupIds[0]}
                            onGlossGroupIdChange={this._onGlossGroupIdChange}
                            onSpeechIdChange={this._onSpeechIdChange}
                            speechId={speechIds[0]}
                        />
                    </React.Suspense>}
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
            nextState.glossGroupIds = [0];
            nextState.speechIds     = [0];
        }

        this.setState(nextState);
        this._persistState(
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

        this._persistState('reversed', reversed);
        this._beginSearch(/* queryChanged: */ true);
    }

    private _onIncludeOldChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const includeOld = ev.target.checked;
        this.setState({
            includeOld,
        });

        this._persistState('includeOld', includeOld);
        this._beginSearch(/* queryChanged: */ false);
    }

    private _onLanguageChange = (ev: IComponentEvent<number>) => {
        const languageId = ev.value;
        this.setState({
            languageId,
        });

        this._persistState('languageId', languageId);
        this._beginSearch(/* queryChanged: */ false);
    }

    private _onGlossGroupIdChange = (ev: IComponentEvent<number>) => {
        const glossGroupIds = [ev.value || 0];
        this.setState({
            glossGroupIds,
        });

        this._persistState('glossGroupIds', glossGroupIds);
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
        this.props.dispatch(
            this._actions.selectNextResult(ev.value),
        );
    }

    /**
     * Performs a keyword search operation, *and* refreshes the glossary if filters
     * changed.
     */
    private _search(queryChanged: boolean) {
        const state = this.state;
        this.props.dispatch(
            this._actions.search({
                ...state,
                // These hacks only accommodates for the fact that the UI does not currently support
                // multiple selections.
                glossGroupIds: state.glossGroupIds[0] === 0 ? [] : state.glossGroupIds,
                speechIds: state.speechIds[0] === 0 ? [] : state.speechIds,
            }),
        );

        // If the user has only made changes to the filtering functions (such as language selection),
        // *and* has a previous glossary already loaded, the user expects the changes to their configuration
        // to reflect to the glossary currently loaded.
        if (queryChanged === false && this.props.currentGlossaryWord?.length > 0) {
            this.props.dispatch(
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
    reversed: state.search.reversed,
    word: state.search.word,
});

export default connect(mapStateToProps)(SearchQuery);
