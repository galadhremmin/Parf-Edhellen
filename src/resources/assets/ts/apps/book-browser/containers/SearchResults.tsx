import classNames from 'classnames';
import React, { useEffect, useRef, useState } from 'react';
import { connect } from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';
import Quote from '@root/components/Quote';
import StaticAlert from '@root/components/StaticAlert';
import Cache from '@root/utilities/Cache';
import { SearchActions } from '../actions';
import LoadingIndicator from '../components/LoadingIndicator';
import SearchGroup from '../components/SearchGroup';
import { RootReducer } from '../reducers';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import { IProps } from './SearchResults._types';

const EnableTipsCacheKey = 'search-results.tips-enabled';

export function SearchResults(props: IProps) {
    const [ enableTips, setEnableTips ] = useState(true);

    const enableTipsCacheRef = useRef<Cache<boolean>>();
    const actionsRef = useRef<SearchActions>();
    const containerRef = useRef<HTMLDivElement>();

    const {
        dispatch,
        loading = true,
        reversed,
        searchGroups = [],
        searchResults = [],
        selectedResultId = 0,
        word = '',
    } = props;

    useEffect(() => {
        // SessionStorage is sometimes unavailable, like within privacy mode or unit testing mode. Provide a graceful
        // fallback in these situations.
        enableTipsCacheRef.current = Cache.withTransientStorage(() => Promise.resolve(true), EnableTipsCacheKey);
        enableTipsCacheRef.current?.get().then((nextEnableTips: boolean) => {
            setEnableTips(nextEnableTips);
        }).catch (err => {
            console.warn(err);
            setEnableTips(true);
        });

        actionsRef.current = new SearchActions();

        return () => {
            // TODO: clean-up?
        }
    }, []);

    /**
     * Event handler for the `SearchResult` component - dispatches a selection action
     * for the specified search result.
     */
    const _onClick = (ev: IComponentEvent<ISearchResult>) => {
        const searchResult = ev.value;
        const payload = {
            searchResult,
            updateBrowserHistory: true,
        };
        void dispatch(actionsRef.current?.expandSearchResult(payload));
        _onDismissInstructions();
    }

    const  _onDismissInstructions = () => {
        setEnableTips(false);
        enableTipsCacheRef.current?.set(false);
    }

    const  _onNextSearchResult = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        void dispatch(actionsRef.current?.selectNextResult(+1));
    }

    const  _onPreviousSearchResult = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        void dispatch(actionsRef.current?.selectNextResult(-1));
    }

    const searching = props.word.length > 0;
    if (!searching) {
        // Render nothing if there are no search results to show.
        return null;
    }

    const searchResultContainerStyles = classNames(
        'results-panel',
        { hidden: searchResults.length < 1 && !loading },
    );
    const noSearchResultsStyles = classNames('results-empty',
        { hidden: searchResults.length > 0 || loading },
    );
    const navigatorStyles = classNames('search-result-navigator',
        { hidden: searchResults.length < 1 },
    );

    return <section>
        <div ref={containerRef} className="shadow-lg p-3 mb-5 bg-body rounded mt-4">
            <h3>
                Search results
            </h3>
            <div className={searchResultContainerStyles}>
                {enableTips && <StaticAlert type="info" dismissable={true} onDismiss={_onDismissInstructions}>
                    These words match <strong>{word}</strong>. Click on the one most relevant to you,
                    or simply press enter to go to the first result in the list.
                </StaticAlert>}
                {loading && <LoadingIndicator text="Browsing the dictionary..." />}
                {searchGroups.map((group, i) => <SearchGroup
                    groupName={group}
                    onClick={_onClick}
                    key={group}
                    searchResults={searchResults[i]}
                    selectedResultId={selectedResultId}
                />)}
                <div className={navigatorStyles}>
                    <nav className="container-fluid">
                        <div className="row">
                            <div className="col">
                                <a className="btn btn-light" href="#" onClick={_onPreviousSearchResult}>&larr; Previous</a>
                            </div>
                            <div className="col text-end">
                                <a className="btn btn-light" href="#" onClick={_onNextSearchResult}>Next &rarr;</a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
            <div className={noSearchResultsStyles}>
                <StaticAlert type="warning">
                    No words matching <strong><Quote>{reversed ? word.split('').reverse().join('') : word}</Quote></strong>
                    {reversed ? <em> (<Quote>{word}</Quote> reversed) </em> : ' '}
                    were found in the dictionary. Have you tried synonyms or perhaps antonyms?
                </StaticAlert>
            </div>
        </div>
    </section>;
}

const mapStateToProps = (state: RootReducer): IProps => ({
    loading: state.search.loading,
    reversed: state.search.reversed,
    searchGroups: state.searchResults.groups,
    searchResults: state.searchResults.resultsByGroupIndex,
    selectedResultId: state.searchResults.selectedId,
    word: state.search.word,
});

export default connect(mapStateToProps)(SearchResults);
