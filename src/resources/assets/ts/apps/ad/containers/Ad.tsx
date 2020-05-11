import React from 'react';
import { toSnakeCase } from '@root/utilities/func/snake-case';
import { IGlobalAdConfiguration } from '../index._types';

function Ad(props: IGlobalAdConfiguration) {
    const {
        dataset,
    } = props;

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
