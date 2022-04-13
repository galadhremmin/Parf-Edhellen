import React from 'react';

import TextIcon from '@root/components/TextIcon';
import { IProps } from './NumberOfComments._types';

const NumberOfComments = (props: IProps) => {
    const { gloss } = props;

    return <a href={`/wt/${gloss.id}`} className="ed-comments-no"
        title="See all versions and read comments">
        <TextIcon icon="comment" />
        {' '}
        <span className="no">{gloss.commentCount}</span>
    </a>;
};

export default NumberOfComments;
