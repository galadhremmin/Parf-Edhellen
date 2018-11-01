import React from 'react';
import { IProps } from './NeologismIndicator._types';

const NeologismIndicator = (props: IProps) => {
    const { gloss } = props;

    if (gloss.isCanon && !gloss.isUncertain) {
        return null;
    }

    return <span className="uncertain" title="Uncertain or possibly a neologism">
        *
    </span>;
};

export default NeologismIndicator;
