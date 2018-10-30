import React from 'react';
import { connect } from 'react-redux';

import { IComponentEvent } from '../../../components/Component._types';
import SharedReference from '../../../utilities/SharedReference';
import { SearchActions } from '../actions';
import { ISearchResult } from '../reducers/SearchResultsReducer._types';
import SearchResult from './SearchResult';
import { IProps } from './SearchResultsContainer._types';

export class SearchResultsContainer extends React.PureComponent<IProps> {

    static get defaultProps() {
        return {
            searchResults: [],
        } as IProps;
    }

    private _actions = new SharedReference(SearchActions);

    public render() {
        return <ul className="search-result">
            {this.props.searchResults.map((result) => <li key={result.id}>
                <SearchResult searchResult={result} onClick={this._onClick} />
            </li>)}
        </ul>;
    }

    private _onClick = (ev: IComponentEvent<ISearchResult>) => {
        this.props.dispatch(this._actions.value.selectSearchResult({
            id: ev.value.id,
        }));
    }
}

const mapStateToProps = (state: any) => ({
    searchResults: state.searchResults,
});

export default connect(mapStateToProps)(SearchResultsContainer);
