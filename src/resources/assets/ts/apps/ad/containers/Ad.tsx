import React, { useEffect } from 'react';
import { toSnakeCase } from '@root/utilities/func/snake-case';
import { IProps } from './Ad._types';
import { IProps as IAppProps } from '../index._types';

function Ad(props: IProps & IAppProps) {
    const {
        ad,
        dataset,
        onMount,
    } = props;

    useEffect(() => {
        if (typeof onMount === 'function') {
            onMount();
        }
    }, [ ad ]);

    let data = {};
    if (typeof dataset === 'object') {
        data = Object.keys(dataset).reduce((carry: Record<string, string>, key: string) => {
            carry[`data-${toSnakeCase(key, '-')}`] = dataset[key];
            return carry;
        }, {} as Record<string, string>);
    }

    return <ins {...props.props} {...data}></ins>;
}

export default Ad;
