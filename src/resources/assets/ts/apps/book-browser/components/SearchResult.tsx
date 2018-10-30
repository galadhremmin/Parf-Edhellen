import React from 'react';

import { IProps } from './SearchResult._types';

class SearchResult extends React.PureComponent<IProps> {
    public render() {
        const r = this.props.searchResult;
        const className = r.selected ? 'selected' : undefined;

        return <a href="#" className={className} onClick={this._onClick}>
            <span className="word">{r.originalWord || r.word}</span>
            {r.originalWord && r.originalWord !== r.word && <span className="development">{r.word}</span>}
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
