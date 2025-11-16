import type { MouseEvent } from 'react';
import { fireEvent } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import type { IProps } from './ActionLink._types';

export default function ActionLink(props: IProps) {
    const {
        children,
        icon,
        onClick,
    } = props;

    const _onClick = (ev: MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        void fireEvent('ActionLink', onClick, null);
    }

    return <a href="#" onClick={_onClick}>
        <TextIcon icon={icon} />
        {' '}
        {children}
    </a>;
}
