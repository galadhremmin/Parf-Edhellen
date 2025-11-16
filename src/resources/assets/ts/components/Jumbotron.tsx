import { type CSSProperties, useEffect, useState } from 'react';
import type { IProps } from './Jumbotron._types';

import classNames from 'classnames';
import './Jumbotron.scss';

export default function Jumbotron({
    backgroundImageUrl = null,
    children,
    className,
}: IProps) {
    const [ computedStyle, setComputedStyle ] = useState<CSSProperties>(null);
    useEffect(() => {
        if (! backgroundImageUrl) {
            setComputedStyle(null);
        } else {
            setComputedStyle({
                backgroundImage: `url(${backgroundImageUrl})`,
            });
        }
    }, [ backgroundImageUrl ]);

    return <div className={classNames('Jumbotron', className, { 'has-background': !! computedStyle })}
        style={computedStyle}>
        {children}
    </div>
}
