import React from 'react';

import { IProps } from './NumberOfComments._types';

const NumberOfComments = (props: IProps) => {
    const { gloss } = props;

    return <a href={`/wt/${gloss.id}/versions`} className="ed-comments-no"
        title="See all versions and read comments">
        <span className="glyphicon glyphicon-comment" />{' '}
        <span className="no">{gloss.commentCount}</span>
    </a>;
};

export default NumberOfComments;
