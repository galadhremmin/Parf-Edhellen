import React from 'react';
import { fireEvent } from '@root/components/Component';
import { IProps } from './SearchResult._types';

export default function SearchResult(props: IProps) {
    const {
        onClick,
        searchResult: r,
        selected,
    } = props;

    /**
     * onClick handler for word links. Disables the default behavior and transforms the event to an event
     * that adheres to the component's public interface.
     */
    const _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        void fireEvent('SearchResult', onClick, r);
    }

    const className = selected ? 'selected' : undefined;

    return <a href="#" className={className} onClick={_onClick}>
        <span className="word">{r.originalWord || r.word}</span>
        {r.originalWord && r.originalWord !== r.word && <span className="development">{r.word}</span>}
    </a>;
}
