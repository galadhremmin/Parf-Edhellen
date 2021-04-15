import classNames from 'classnames';
import React from 'react';
import { connect } from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';
import StaticAlert from '@root/components/StaticAlert';
import Cache from '@root/utilities/Cache';
import { SearchActions } from '../actions';
import SearchGroup from '../components/SearchGroup';
import { RootReducer } from '../reducers';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import { IProps } from './SearchResults._types';
import LoadingIndicator from '../components/LoadingIndicator';

export class SearchResults extends React.Component<IProps> {
    static get defaultProps() {
        return {
            loading: true,
            searchGroups: [],
            searchResults: [],
            selectedResultId: 0,
            word: '',
        } as IProps;
    }

    state = {
        enableTips: true,
    };

    private _actions = new SearchActions();
    private _enableTipsCache: Cache<boolean>;

    constructor(props: IProps) {
        super(props);

        // SessionStorage is sometimes unavailable, like within privacy mode or unit testing mode. Provide a graceful
        // fallback in these situations.
        const enableTipsCacheKey = 'search-results.tips-enabled';
        try {
            this._enableTipsCache = Cache.withSessionStorage(() => Promise.resolve(true), enableTipsCacheKey);
        } catch (ex) {
            console.warn(ex);
            this._enableTipsCache = Cache.withMemoryStorage(() => Promise.resolve(true), enableTipsCacheKey);
        }
    }

    public async componentDidMount() {
        const enableTips = await this._enableTipsCache.get();
        if (enableTips !== this.state.enableTips) {
            this.setState({
                enableTips,
            });
        }
    }

    public render() {
        const searching = this.props.word.length > 0;
        if (!searching) {
            // Render nothing if there are no search results to show.
            return null;
        }

        const {
            loading,
            searchGroups,
            searchResults,
            selectedResultId,
        } = this.props;

        const {
            enableTips,
        } = this.state;

        const searchResultContainerStyles = classNames(
            'panel-body', 'results-panel',
            { hidden: searchResults.length < 1 && !loading },
        );
        const noSearchResultsStyles = classNames('panel-body', 'results-empty',
            { hidden: searchResults.length > 0 || loading },
        );
        const navigatorStyles = classNames('search-result-navigator',
            { hidden: searchResults.length < 1 },
        );

        return <section>
            <div className="panel panel-default search-result-wrapper">
                <div className="panel-heading">
                    <h3 className="panel-title search-result-wrapper-toggler-title">
                        Search results
                    </h3>
                </div>
                <div className={searchResultContainerStyles}>
                    {enableTips && <StaticAlert type="info" dismissable={true} onDismiss={this._onDismissInstructions}>
                        These words match <strong>{this.props.word}</strong>. Click on the one most relevant to you,
                        or simply press enter to go to the first result in the list.
                    </StaticAlert>}
                    {loading && <LoadingIndicator text="Browsing the dictionary..." />}
                    {searchGroups.map((group, i) => <SearchGroup
                        groupName={group}
                        onClick={this._onClick}
                        key={group}
                        searchResults={searchResults[i]}
                        selectedResultId={selectedResultId}
                    />)}
                    <div className={navigatorStyles}>
                        <nav>
                            <ul className="pager">
                                <li className="previous">
                                    <a href="#" onClick={this._onPreviousSearchResult}>&larr; Previous</a>
                                </li>
                                <li className="next">
                                    <a href="#" onClick={this._onNextSearchResult}>Next &rarr;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div className={noSearchResultsStyles}>
                    <StaticAlert type="warning">
                        The dictionary unfortunately does not contain words that match <strong>{this.props.word}</strong>. Have you tried a synonym or perhaps even an antonym?
                    </StaticAlert>
                </div>
            </div>
        </section>;
    }

    /**
     * Event handler for the `SearchResult` component - dispatches a selection action
     * for the specified search result.
     */
    private _onClick = (ev: IComponentEvent<ISearchResult>) => {
        const {
            dispatch,
        } = this.props;
        const searchResult = ev.value;
        const payload = {
            searchResult,
            updateBrowserHistory: true,
        };
        dispatch(this._actions.expandSearchResult(payload));
        this._onDismissInstructions();
    }

    private _onDismissInstructions = () => {
        this.setState({
            enableTips: false,
        });

        this._enableTipsCache.set(false);
    }

    private _onNextSearchResult = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        this.props.dispatch(this._actions.selectNextResult(+1));
    }

    private _onPreviousSearchResult = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        this.props.dispatch(this._actions.selectNextResult(-1));
    }
}

const mapStateToProps = (state: RootReducer): IProps => ({
    loading: state.search.loading,
    searchGroups: state.searchResults.groups,
    searchResults: state.searchResults.resultsByGroupIndex,
    selectedResultId: state.searchResults.selectedId,
    word: state.search.word,
});

export default connect(mapStateToProps)(SearchResults);
