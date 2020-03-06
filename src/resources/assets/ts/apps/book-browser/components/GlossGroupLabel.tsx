import React from 'react';
import { IProps } from './GlossGroupLabel._types';

const GlossGroupLabel = (props: IProps) => {
    const {
        glossGroupLabel,
    } = props.gloss;

    const hasLabel = !! glossGroupLabel;
    if (! hasLabel) {
        return null;
    }

    return <span className="gloss-word__neologism">
        {hasLabel && <span className="label" title={glossGroupLabel}>{glossGroupLabel}</span>}
    </span>;
};

export default GlossGroupLabel;
