import classNames from 'classnames';
import React from 'react';

import { IProps } from './GlossTitle._types';

import GlossAbsoluteLink from './GlossAbsoluteLink';
import NeologismIndicator from './NeologismIndicator';
import NumberOfComments from './NumberOfComments';

const GlossTitle = (props: IProps) => {
    const { gloss } = props;
    const className = classNames({rejected: gloss.isRejected});

    return <h3 className="gloss-word">
        <NeologismIndicator gloss={gloss} />
        <span itemProp="headline" className={className}>
            {gloss.word}
        </span>
        <NumberOfComments gloss={gloss} />
        <GlossAbsoluteLink gloss={gloss} />
    </h3>;
};

export default GlossTitle;
