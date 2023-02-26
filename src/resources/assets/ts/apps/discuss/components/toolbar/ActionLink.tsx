import React from 'react';
import { fireEvent } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import { IProps } from './ActionLink._types';

export default function ActionLink(props: IProps) {
    const {
        children,
        icon,
        onClick,
    } = props;

    const _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEvent('ActionLink', onClick, null);
    }

    return <a href="#" onClick={_onClick}>
        <TextIcon icon={icon} />
        {' '}
        {children}
    </a>;
}
