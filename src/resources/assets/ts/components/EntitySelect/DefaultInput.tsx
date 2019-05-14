import classNames from 'classnames';
import React from 'react';

import { InputProps } from 'react-autosuggest';

function DefaultInput<T>(props: InputProps<T>) {
    props.className = classNames('form-control', props.className);
    return <input {...(props as any)} />;
}

export default DefaultInput;
