import React from 'react';

import { IProps } from './Language._types';

import './Language.scss';

function Language(props: IProps) {
    const {
        language,
    } = props;

    return <>
        <h2 className="Language__header">
            { language.isUnusual ? '† ' : '' }
            { language.name }
            &nbsp;
            <span className="tengwar">{ language.tengwar }</span>
        </h2>
        {language.category && <h3 className="Language__subheader">{language.category}</h3>}
    </>;
}

export default Language;
