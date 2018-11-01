import React from 'react';

import Tengwar from '../../../components/Tengwar';
import { IProps } from './GlossTranslations._types';

const GlossTranslations = (props: IProps) => <p>
    <Tengwar text={props.gloss.tengwar} />
    {' '}
    {props.gloss.type && <span className="word-type">{props.gloss.type}.</span>}
    {' '}
    <span itemProp="keywords">
        {props.gloss.allTranslations}
    </span>
</p>;

export default GlossTranslations;
