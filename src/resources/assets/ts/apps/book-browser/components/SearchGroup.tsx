import React from 'react';

import SearchResult from './SearchResult';
import { IProps } from './SearchGroup._types';

import './SearchGroup.scss';

function SearchGroup(props: IProps) {
    const {
        groupName,
        onClick,
        searchResults,
        selectedResultId,
    } = props;

    if (!searchResults?.length) {
        return null;
    }

    return <>
        <h3 className="SearchGroup__group-name">{groupName}</h3>
        <ul className="search-result">
            {searchResults.map((result) => <li key={result.id}>
                <SearchResult searchResult={result} onClick={onClick} selected={selectedResultId === result.id} />
            </li>)}
        </ul>
    </>;
}

export default SearchGroup;
