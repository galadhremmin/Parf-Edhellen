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

    return <div className={classNames('input-group', { 'has-warning': !valid && props.required, 'has-success': valid })}>
        <input {...inputProps} />
        <div className="input-group-addon">
            <span className={classNames('glyphicon', { 'glyphicon-exclamation-sign': !valid && this.props.required, 'glyphicon-ok': valid })} />
        </div>
    </div>;
}

export default Input;
