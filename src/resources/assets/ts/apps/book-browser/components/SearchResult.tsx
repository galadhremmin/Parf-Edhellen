import React from 'react';

import { fireEvent } from '@root/components/Component';
import { IProps } from './SearchResult._types';

class SearchResult extends React.Component<IProps> {
    public render() {
        const r = this.props.searchResult;
        const className = r.selected ? 'selected' : undefined;

        return <a href="#" className={className} onClick={this._onClick}>
            <span className="word">{r.originalWord || r.word}</span>
            {r.originalWord && r.originalWord !== r.word && <span className="development">{r.word}</span>}
        </a>;
    }

    /**
     * onClick handler for word links. Disables the default behavior and transforms the event to an event
     * that adheres to the component's public interface.
     */
    private _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        fireEvent(this, this.props.onClick, this.props.searchResult);
    }
}

export default SearchResult;
