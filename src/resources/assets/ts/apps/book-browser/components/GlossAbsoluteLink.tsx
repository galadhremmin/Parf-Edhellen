import React from 'react';

import TextIcon from '@root/components/TextIcon';
import { IProps } from './GlossAbsoluteLink._types';

const GlossAbsoluteLink = (props: IProps) => {
    const { gloss } = props;

    return <a href={`/wt/${gloss.id}`} className="gloss-link">
        <TextIcon icon="share" />
    </a>;
};

export default GlossAbsoluteLink;
