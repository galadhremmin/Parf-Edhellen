import React from 'react';

import { fireEvent } from '../Component';
import { IProps } from './PaginationLink._types';

export default class PaginationLink extends React.Component<IProps> {
    public static defaultProps = {
        onClick: null,
        parameterName: 'offset',
    } as Partial<IProps>;

    public render() {
        const {
            children,
            pageNumber,
        } = this.props;

        return <a href={this._createLink()} onClick={this._onClick}>
            {children || pageNumber}
        </a>;
    }

    private _createLink() {
        const {
            pageNumber,
            parameterName,
        } = this.props;

        return `?${parameterName}=${pageNumber}`;
    }

    private _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        const {
            onClick,
            pageNumber,
        } = this.props;

        if (onClick !== null) {
            ev.preventDefault();
            fireEvent(this, onClick, pageNumber);
        }
    }
}
