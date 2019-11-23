import classNames from 'classnames';
import React from 'react';
import { connect } from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';
import SharedReference from '@root/utilities/SharedReference';
import { SearchActions } from '../actions';
import SearchResult from '../components/SearchResult';
import { RootReducer } from '../reducers';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import { IProps } from './SearchResults._types';

export class SearchResults extends React.PureComponent<IProps> {
    static get defaultProps() {
        return {
            searchResults: [],
            word: '',
        } as IProps;
    }

    private _actions = new SharedReference(SearchActions);

    public render() {
        const searching = this.props.word.length > 0;
        if (!searching) {
            // Render nothing if there are no search results to show.
            return null;
        }

        const { searchResults } = this.props;

        const searchResultContainerStyles = classNames(
            'panel-body', 'results-panel',
            { hidden: searchResults.length < 1 },
        );
        const noSearchResultsStyles = classNames('panel-body', 'results-empty',
            { hidden: searchResults.length > 0 },
        );
        const navigatorStyles = classNames('row', 'search-result-navigator',
            { hidden: searchResults.length < 2 },
        );

        return <section>
            <div className="panel panel-default search-result-wrapper">
                <div className="panel-heading">
                    <h3 className="panel-title search-result-wrapper-toggler-title">
                        Search results
                    </h3>
                </div>
                <div className={searchResultContainerStyles}>
                    <div className="row">
                        <div className="col-xs-12">
                            These words match <em>{this.props.word}</em>. Click on the one most relevant to you,
                            or simply press enter to expand the first item in the list.
                        </div>
                    </div>
                    <div className="row">
                        <ul className="search-result">
                            {this.props.searchResults.map((result) => <li key={result.id}>
                                <SearchResult searchResult={result} onClick={this._onClick} />
                            </li>)}
                        </ul>
                    </div>
                </div>
                <div className={noSearchResultsStyles}>
                    <div className="row">
                        <div className="col-xs-12">
                            Unfortunately, we were unable to find any words matching <em>{this.props.word}</em>.
                            Have you tried a synonym, or perhaps even an antonym?
                        </div>
                    </div>
                </div>
            </div>
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
        </section>;
    }

    /**
     * Event handler for the `SearchResult` component - dispatches a selection action
     * for the specified search result.
     */
    private _onClick = (ev: IComponentEvent<ISearchResult>) => {
        this.props.dispatch(this._actions.value.glossary({
            searchResult: ev.value,
            updateBrowserHistory: true,
        }));
    }

    private _onNextSearchResult = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        this.props.dispatch(this._actions.value.selectNextResult(+1));
    }

    private _onPreviousSearchResult = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        this.props.dispatch(this._actions.value.selectNextResult(-1));
    }
}

const mapStateToProps = (state: RootReducer) => ({
    searchResults: state.searchResults,
    word: state.search.word,
});

export default connect(mapStateToProps)(SearchResults);
