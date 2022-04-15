import React from 'react';

import StaticAlert from '../StaticAlert';
import TextIcon from '../TextIcon';
import { IProps } from './ValidationErrorAlert._types';

const ValidationErrorAlert = (props: IProps) => {
    const {
        error,
    } = props;

    if (error === null) {
        return null;
    }

    if (typeof error === 'string') {
        // Something went really, really bad; probably 500 Server Error, so fallback on
        // a red, menacing alert:
        return <StaticAlert type="danger">
            <TextIcon icon="warning-sign" />
            {` ${error}`}
        </StaticAlert>;
    }

    if (error.errors.size < 1) {
        return <StaticAlert type="warning">
            {error.errorMessage}
        </StaticAlert>;
    }

    const errors: React.ReactNode[] = [];
    error.errors.forEach((propertyErrors, propertyName) => {
        for (const propertyError of propertyErrors) {
            errors.push(<span key={errors.length}>
                <em>{propertyName}</em>: {propertyError}
            </span>);
        }
    });

    const modifiedMessage = /[!\?\.:]{1}$/.test(error.errorMessage)
        ? error.errorMessage.substring(0, error.errorMessage.length - 1)
        : error.errorMessage;

    return <StaticAlert type="warning">
        <strong>{modifiedMessage}:</strong>
        {errors.map((err, i) => <li key={i}>{err}</li>)}
    </StaticAlert>;
};

export default ValidationErrorAlert;
