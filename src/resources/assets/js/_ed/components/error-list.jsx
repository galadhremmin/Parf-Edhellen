import React from 'react';

const EDErrorList = props => {
    const errors = props.errors;

    if (!Array.isArray(errors)) {
        return <div className="zero-errors"></div>;
    }

    return <div className="alert alert-danger">
        <strong>Nae!</strong> The server was not able to process your request. Please fix the following
        errors in order to proceed:
        <ul>
            {errors.map((error, i) => <li key={i}>{error}</li>)}
        </ul>
    </div>;
}

export default EDErrorList;
