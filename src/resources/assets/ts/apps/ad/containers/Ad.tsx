import React from 'react';
import { IProps } from './Ad._types';

function Ad(props: IProps) {
    return <ins {...props.config}></ins>;
}

export default Ad;
