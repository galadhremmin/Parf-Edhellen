/* eslint-disable @typescript-eslint/no-unsafe-member-access */
import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { excludeProps } from '@root/utilities/func/props';
import {
    IdValue,
    IProps,
    ValueType,
} from './AsyncSelect._types';
import useFetch from './fetch';

const InternalProps: (keyof IProps)[] = [
    'allowEmpty',
    'emptyText',
    'loaderOfValues',
    'onChange',
    'textField',
    'value',
    'valueField',
    'valueType',
];

function AsyncSelect<T = any>(props: IProps<T>) {
    const componentProps = excludeProps<Partial<IProps<T>>>(props, InternalProps);

    const {
        allowEmpty,
        emptyText,
        loaderOfValues,
        name,
        onChange,
        textField,
        value,
        valueField,
        valueType,
    } = props;

    const values = useFetch(loaderOfValues, value);

    const _onChange = useCallback((ev: React.ChangeEvent<HTMLSelectElement>) => {
        const newValue = getDesiredValue(
            values.find((v) => v[valueField] as any == ev.target.value),
            valueType,
            valueField as string,
        );

        fireEvent(name, onChange, newValue ? newValue : null);
    }, [ name, onChange, values, valueField ]);

    return <select {...componentProps}
        id={name}
        onChange={_onChange}
        value={getNativeValue(value, valueField as string)}>
        {allowEmpty && <option key="empty" value="">{emptyText || ''}</option>}
        {values.map((option) => {
            const optionValue = option[valueField] as any;
            return <option key={optionValue} value={optionValue}>{option[textField]}</option>;
        })}
    </select>;
}

AsyncSelect.defaultProps = {
    allowEmpty: false,
    valueType: 'entity',
} as IProps;

function getNativeValue(v: any, valueField: string): IdValue {
    if (v === null || v === undefined) {
        return '';
    }

    if (typeof v === 'object') {
        return v[valueField] as IdValue;
    }

    return v;
}

function getDesiredValue(v: any, valueType: ValueType, valueField: string): IdValue {
    if (v === null || v === undefined) {
        return null;
    }

    switch (valueType) {
        case 'id':
            return v[valueField];
        case 'entity':
            return v;
        default:
            return null;
    }
}

export default AsyncSelect;
