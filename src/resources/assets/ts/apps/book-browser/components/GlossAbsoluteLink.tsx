import React from 'react';

import { IProps } from './GlossAbsoluteLink._types';

const GlossAbsoluteLink = (props: IProps) => {
    const { gloss } = props;

    return <a href={`/wt/${gloss.id}`} className="gloss-link">
        <span className="glyphicon glyphicon-share"></span>
    </a>;
};

export default GlossAbsoluteLink;
