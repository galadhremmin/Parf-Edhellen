import React from 'react';
import { fireEventAsync } from '@root/components/Component';
import { IProps } from './Fragment._types';

export default function Fragments(props: IProps) {
    const {
        fragment,
        onClick,
        selected,
    } = props;

    const _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        if (typeof onClick === 'function') {
            void fireEventAsync('Fragment', onClick, fragment);
        }
    }

    if (fragment?.id !== 0) {
        return <a href={`#${fragment.id}`}
            className={selected ? 'selected' : undefined}
            onClick={_onClick}>
            {fragment.fragment}
        </a>;
    }

    return <span>{fragment?.fragment}</span>;
}
