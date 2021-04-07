import React from 'react';

import { IProps } from './Language._types';

function Language(props: IProps) {
    const {
        language,
    } = props;

    return <h2>
        { language.isUnusual ? '† ' : '' }
        { language.name }
        &nbsp;
        <span className="tengwar">{ language.tengwar }</span>
    </h2>;
}

export default Language;
