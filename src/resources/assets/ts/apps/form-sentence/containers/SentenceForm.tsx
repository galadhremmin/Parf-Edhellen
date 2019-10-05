import React from 'react';

import { IProps } from './SentenceForm._types';

function SentenceForm(props: IProps) {
    const {
        sentence,
    } = props;

    return <pre>
        {JSON.stringify(sentence, undefined, 2)}
    </pre>;
}

export default SentenceForm;
