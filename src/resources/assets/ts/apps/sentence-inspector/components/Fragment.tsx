import React from 'react';
import { IProps } from './Fragment._types';

class Fragments extends React.PureComponent<IProps> {
    public render() { 
        const {
            fragment,
            selected,
        } = this.props;

        if (fragment.id > -1) {
            return <a href={`#${fragment.id}`}
                className={selected ? 'selected' : undefined}
                onClick={this._onClick}>
                {fragment.fragment}
            </a>;
        }
    
        return <span>{fragment.fragment}</span>;
    }

    private _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        const {
            fragment,
            onClick,
        } = this.props;

        if (typeof onClick === 'function') {
            onClick(fragment);
        }
    }
}

export default Fragments;
