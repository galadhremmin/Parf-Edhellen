import classNames from 'classnames';
import React from 'react';

import { RenderInputComponentProps } from 'react-autosuggest';

function DefaultInput(props: RenderInputComponentProps) {
    props.className = classNames('form-control', props.className);
    return <input {...(props as any)} />;
}

export default DefaultInput;
