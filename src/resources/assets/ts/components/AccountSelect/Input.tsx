import classNames from 'classnames';
import React, { useState } from 'react';

import { IProps } from './Input._types';

function Input(props: IProps) {
    const [ value, setValue ] = useState('');

    const inputProps = {
        ...props,
        className: `form-control ${props.className}`,
        value,
    };

    return <div>
        <input {...inputProps} />
    </div>;
}

export default Input;
