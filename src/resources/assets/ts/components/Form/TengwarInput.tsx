import classNames from 'classnames';
import React, { useCallback } from 'react';

import { excludeProps } from '@root/utilities/func/props';
import { fireEvent } from '../Component';
import { IComponentProps } from './FormComponent._types';

function TengwarInput(props: IComponentProps<string>) {
    const {
        className,
        onChange,
        name,
        value,
    } = props;

    const componentProps = excludeProps(props, ['className', 'onChange']);
    const componentClassName = classNames('form-control', 'tengwar', className || '');

    const _onChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        fireEvent(name, onChange, ev.target.value);
    }, [ name, onChange, value ]);

    return <input type="text"
        {...componentProps}
        className={componentClassName}
        onChange={_onChange}
    />;
}

TengwarInput.defaultProps = {
    value: '',
} as IComponentProps<string>;

export default TengwarInput;
