import classNames from 'classnames';
import React from 'react';

import { IProps } from './GlossTitle._types';
import NeologismIndicator from './NeologismIndicator';

const GlossTitle = (props: IProps) => {
    const { gloss } = props;
    const className = classNames({'rejected': gloss.isRejected});

    return <React.Fragment>
        <NeologismIndicator gloss={gloss} />
        <span itemProp="headline" className={className}>
            {gloss.word}
        </span>
    </React.Fragment>;
};

export default GlossTitle;
