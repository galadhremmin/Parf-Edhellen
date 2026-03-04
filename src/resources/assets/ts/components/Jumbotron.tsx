import { type CSSProperties, useEffect, useState } from 'react';
import type { IProps } from './Jumbotron._types';

import classNames from '@root/utilities/ClassNames';
import './Jumbotron.scss';

export default function Jumbotron({
    backgroundImageUrl = null,
    backgroundMobileImageUrl = null,
    children,
    className,
}: IProps) {
    const [ computedStyle, setComputedStyle ] = useState<CSSProperties>(null);
    const [ isCustomBackground, setIsCustomBackground ] = useState(false);

    useEffect(() => {
        if (! backgroundImageUrl) {
            setComputedStyle(null);
            setIsCustomBackground(false);
        } else if (backgroundMobileImageUrl) {
            setComputedStyle({
                '--bg-desktop': `url(${backgroundImageUrl})`,
                '--bg-mobile': `url(${backgroundMobileImageUrl})`,
            } as CSSProperties);
            setIsCustomBackground(true);
        } else {
            setComputedStyle({
                backgroundImage: `url(${backgroundImageUrl})`,
            });
            setIsCustomBackground(false);
        }
    }, [ backgroundImageUrl, backgroundMobileImageUrl ]);

    return <div className={classNames('Jumbotron', className, {
            'has-background': !! computedStyle,
            'has-custom-background': isCustomBackground,
        })}
        style={computedStyle}>
        {children}
    </div>
}
