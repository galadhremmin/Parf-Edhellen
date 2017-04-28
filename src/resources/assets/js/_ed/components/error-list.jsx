import React from 'react';

const EDErrorList = props => {
    const errors = props.errors;

    if (!Array.isArray(errors)) {
        return <div className="zero-errors"></div>;
    }

    return <div className="alert alert-danger">
        <ul>
            {errors.map((error, i) => <li key={i}>{error}</li>)}
        </ul>
    </div>;
}

export default EDErrorList;
