import React from 'react';

import {
    IProps,
} from './SearchResults.types';

class SearchResult extends React.PureComponent<IProps> {
    public render() {
        const r = this.props.searchResult;
        const className = r.selected ? 'selected' : undefined;

        return <a href="#" className={className} onClick={this._onClick}>
            <span>{r.originalWord || r.word}</span>
            <span>{r.originalWord && r.originalWord !== r.word && ` ⇨ ${r.word}`}</span>
        </a>;
    }

    private _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        const onClick = this.props.onClick;
        if (typeof onClick === 'function') {
            onClick({
                value: this.props.searchResult,
            });
        }
    }
}

export default SearchResult;
