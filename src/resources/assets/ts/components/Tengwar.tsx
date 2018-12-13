import React from 'react';
import { IProps } from './Tengwar._types';

import './Tengwar.scss';

const Tengwar = (props: IProps) => {
    const className = 'tengwar';
    const Component = props.as as any;
    const text = props.text;

    if (!text) {
        return null;
    }

    return <Component className={className}>{text}</Component>;
};

Tengwar.defaultProps = {
    as: 'span',
};

export default Tengwar;
