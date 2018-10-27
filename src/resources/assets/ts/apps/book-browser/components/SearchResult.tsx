import React from 'react';

import {
    IProps,
} from './SearchResults.types';

const SearchResult = (props: IProps) => {
    const r = props.searchResult;
    return <React.Fragment>
        <a href="#" className={undefined} onClick={props.onClick}>
            {r.originalWord || r.word}
        </a>
        {r.originalWord && r.originalWord !== r.word && ` ⇨ ${r.word}`}
    </React.Fragment>;
};

export default SearchResult;
