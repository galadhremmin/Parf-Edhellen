import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { excludeProps } from '@root/utilities/func/props';
import { IProps } from './AsyncSelect._types';
import useFetch from './fetch';

const InternalProps: Array<keyof IProps> = [
    'loaderOfValues', 'onChange', 'textField', 'value', 'valueField',
];

function AsyncSelect<T = any>(props: IProps<T>) {
    const componentProps = excludeProps(props, InternalProps);

    const {
        name,
        loaderOfValues,
        onChange,
        textField,
        value,
        valueField,
    } = props;

    const values = useFetch(loaderOfValues, value);

    const _onChange = useCallback((ev: React.ChangeEvent<HTMLSelectElement>) => {
        const newValue = values.find((v) => v[valueField] as any === ev.target.value);
        fireEvent(name, onChange, newValue ? newValue : null);
    }, [ name, onChange, values, valueField ]);

    return <select {...componentProps}
        id={name}
        onChange={_onChange}
        value={value ? (value[valueField] as any) : ''}>
        {values.map((option) => {
            const optionValue = option[valueField] as any;
            return <option key={optionValue} value={optionValue}>{option[textField]}</option>;
        })}
    </select>;
}

export default AsyncSelect;
