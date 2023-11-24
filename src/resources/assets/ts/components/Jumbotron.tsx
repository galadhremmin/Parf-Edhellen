import { CSSProperties, useEffect, useState } from 'react';
import { IProps } from './Jumbotron._types';

import './Jumbotron.scss';
import classNames from 'classnames';

export default function Jumbotron({
    backgroundImageUrl,
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

Jumbotron.defaultProps = {
    backgroundImageUrl: null,
};
