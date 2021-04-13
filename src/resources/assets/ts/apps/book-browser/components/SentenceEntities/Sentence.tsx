import React from 'react';

import { IProps } from './Sentence._types';

function Sentence(props: IProps) {
    const {
        sentence,
    } = props;
    return <blockquote>
        <a className="block-link" href={`/phrases/${sentence.languageId}-l/${sentence.id}-p`}>
            <h3>{sentence.name}</h3>
            <p>{sentence.description}</p>
        </a>
        <footer>
            {sentence.source} by <a href={`/author/${sentence.account?.id}`}>{sentence.account?.nickname}</a>.
        </footer>
    </blockquote>;
}

export default Sentence;
