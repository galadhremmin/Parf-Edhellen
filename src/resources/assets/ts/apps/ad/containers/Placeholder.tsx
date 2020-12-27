import React from 'react';

import { IProps } from '../index._types';

const PlaceholderStyles = {
    border: '1px solid red',
    color: 'red',
    display: 'block',
    'line-height': '140px',
    'text-align': 'center',
    'text-decoration': 'none',
};
function Placeholder(props: IProps) {
    const {
        ad,
    } = props;
    return <ins style={PlaceholderStyles}>{ad}</ins>;
}

export default Placeholder;
