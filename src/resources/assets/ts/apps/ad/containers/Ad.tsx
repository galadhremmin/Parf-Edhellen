import React, { useEffect } from 'react';
import { toSnakeCase } from '@root/utilities/func/snake-case';
import { IProps } from './Ad._types';

function Ad(props: IProps) {
    const {
        dataset,
        onMount,
    } = props;

    useEffect(() => {
        if (typeof onMount === 'function') {
            onMount();
        }
    }, [ onMount ]);

    let data = {};
    if (typeof dataset === 'object') {
        data = Object.keys(dataset).reduce((carry: any, key: string) => {
            carry[`data-${toSnakeCase(key, '-')}`] = dataset[key];
            return carry;
        }, {});
    }

    return <ins {...props.props} {...data}></ins>;
}

export default Ad;
