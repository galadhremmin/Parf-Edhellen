import React from 'react';

import StaticAlert from '@root/components/StaticAlert';
import { IProps } from './SentenceForm._types';

function SentenceForm(props: IProps) {
    const {
        sentence,
    } = props;

    return <StaticAlert type="info">
        Coming soon!
    </StaticAlert>;
}

export default SentenceForm;
