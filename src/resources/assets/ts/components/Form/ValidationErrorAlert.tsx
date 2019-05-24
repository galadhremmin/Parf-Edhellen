import React from 'react';

import StaticAlert from '../StaticAlert';
import { IProps } from './ValidationErrorAlert._types';

const ValidationErrorAlert = (props: IProps) => {
    const {
        error,
    } = props;

    if (error === null) {
        return null;
    }

    if (error.errors.size < 1) {
        return <StaticAlert type="warning">
            {error.errorMessage}
        </StaticAlert>;
    }

    const errors = [];
    for (const [ propertyName, propertyError ] of error.errors) {
        errors.push(`${propertyName}: ${propertyError}`);
    }

    const modifiedMessage = /[!\?\.:]{1}$/.test(error.errorMessage)
        ? error.errorMessage.substr(0, error.errorMessage.length - 1)
        : error.errorMessage;

    return <StaticAlert type="warning">
        <strong>{modifiedMessage}:</strong>
        {errors.map((err, i) => <li key={i}>{err}</li>)}
    </StaticAlert>;
};

export default ValidationErrorAlert;
