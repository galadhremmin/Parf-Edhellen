import React from 'react';
import { connect } from 'react-redux';

import SearchResult from './SearchResult';
import { IProps } from './SearchResultsContainer.types';

class SearchResultsContainer extends React.PureComponent<IProps> {
    static get defaultProps() {
        return {
            searchResults: [],
        } as IProps;
    }

    public render() {
        return <ul>
            {this.props.searchResults.map((result) => <li key={result.id}>
                <SearchResult searchResult={result} onClick={this._onClick} />
            </li>)}
        </ul>;
    }

    private _onClick = () => {
        // TODO
    }
}

const mapStateToProps = (state: any) => ({
    searchResults: state.searchResults,
});

export default connect(mapStateToProps)(SearchResultsContainer);
