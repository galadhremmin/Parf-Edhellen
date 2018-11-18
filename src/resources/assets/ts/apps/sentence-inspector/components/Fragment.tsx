import React from 'react';
import { IProps } from './Fragment._types';

class Fragments extends React.PureComponent<IProps> {
    onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        const {
            fragment,
            onClick,
        } = this.props;

        if (typeof onClick === 'function') {
            onClick(fragment);
        }
    }

    public render() { 
        const {
            fragment,
            selected,
        } = this.props;

        if (fragment.id > -1) {
            return <a href={`#${fragment.id}`}
                className={selected ? 'selected' : undefined}
                onClick={this.onClick}>
                {fragment.fragment}
            </a>;
        }
    
        return <span>{fragment.fragment}</span>;
    }
}

export default Fragments;
