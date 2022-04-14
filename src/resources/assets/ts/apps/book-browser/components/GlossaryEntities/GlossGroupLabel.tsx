import React from 'react';
import { IProps } from './GlossGroupLabel._types';

const GlossGroupLabel = (props: IProps) => {
    const {
        glossGroupLabel,
        label: glossLabel,
    } = props.gloss;

    const label = glossLabel ?? glossGroupLabel;
    if (! label) {
        return null;
    }

    return <span className="gloss-word__neologism">
        <span className="badge rounded-pill badge-sm bg-info position-absolute top-0 start-0 ms-1 translate-middle-y" title={label}>{label}</span>
    </span>;
};

export default GlossGroupLabel;
