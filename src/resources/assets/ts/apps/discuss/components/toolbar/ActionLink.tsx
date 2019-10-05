import React from 'react';

import { fireEvent } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import { IProps } from './ActionLink._types';

export default class ActionLink extends React.PureComponent<IProps> {
    public render() {
        const {
            icon,
        } = this.props;

        return <a href="#" onClick={this._onClick}>
            <TextIcon icon={icon} />
            {' '}
            {this.props.children}
        </a>;
    }

    private _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEvent(this, this.props.onClick, null);
    }
}
