/* eslint-disable @typescript-eslint/no-unsafe-member-access */
import React, { useCallback, useMemo } from 'react';

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
        allowEmpty = false,
        emptyText,
        loaderOfValues,
        name,
        onChange,
        textField,
        value,
        valueField,
        valueType = 'entity',
    } = props;

    const values = useFetch(loaderOfValues, value);

    const options = useMemo(() => 
        values.map((option) => {
            const optionValue = option[valueField] as any;
            // eslint-disable-next-line @typescript-eslint/no-base-to-string
            return <option key={optionValue} value={optionValue}>{String(option[textField])}</option>;
        }), 
        [values, valueField, textField]
    );

    const _onChange = useCallback((ev: React.ChangeEvent<HTMLSelectElement>) => {
        const newValue = getDesiredValue(
            values.find((v) => v[valueField] as any == ev.target.value),
            valueType,
            valueField as string,
        );

        void fireEvent(name, onChange, newValue ? newValue : null);
    }, [ name, onChange, values, valueField, valueType ]);

    return <select {...componentProps}
        id={name}
        onChange={_onChange}
        value={getNativeValue(value, valueField as string)}>
        {allowEmpty && <option key="empty" value="">{emptyText || ''}</option>}
        {options}
    </select>;
}

function getNativeValue(v: any, valueField: string): IdValue {
    if (v === null || v === undefined) {
        return '';
    }

    if (typeof v === 'object') {
        return v[valueField] as IdValue;
    }

    return v as IdValue;
}

function getDesiredValue(v: any, valueType: ValueType, valueField: string): IdValue {
    if (v === null || v === undefined) {
        return null;
    }

    switch (valueType) {
        case 'id':
            return v[valueField] as IdValue;
        case 'entity':
            return v as IdValue;
        default:
            return null;
    }
}

export default AsyncSelect;
