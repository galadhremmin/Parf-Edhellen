import React from 'react';
import { IProps } from './OldVersionAlert._types';

const OldVersionAlert = (props: IProps) => {
    const { gloss } = props;

    if (gloss.isLatest) {
        return null;
    }

    return <p className="alert alert-danger">
        <span className="glyphicon glyphicon-warning-sign"></span>{' '}
        <strong>Important!</strong> A newer version of this gloss was found in the dictionary.
        You should <a href={`/wt/${gloss.id}/latest`}> go to the latest version instead</a>.
    </p>;
};

export default OldVersionAlert;
