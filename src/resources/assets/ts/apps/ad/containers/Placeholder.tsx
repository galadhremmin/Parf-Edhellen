import React, { CSSProperties } from 'react';

import { IProps } from '../index._types';

const PlaceholderStyles: CSSProperties = {
    border: '1px solid red',
    color: 'red',
    display: 'block',
    lineHeight: '140px',
    textAlign: 'center',
    textDecoration: 'none',
};

function Placeholder(props: IProps) {
    const {
        ad,
    } = props;
    return <ins style={PlaceholderStyles}>{ad}</ins>;
}

export default Placeholder;
